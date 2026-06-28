<?php
/**
 * Plugin Name:       Announcement Bar
 * Plugin URI:        https://zubeidhendricks.dev/wp-plugins/announcement-bar
 * Description:        Add a dismissible sticky announcement / notification bar to the top of your site — sales, notices, links — no theme editing.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Zubeid Hendricks
 * Author URI:        https://zubeidhendricks.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       announcement-bar
 *
 * @package AnnouncementBar
 */

defined( 'ABSPATH' ) || exit;

define( 'ANNOUNCEMENT_BAR_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/factory-core.php';

/**
 * Announcement Bar.
 */
final class AnnouncementBar extends ZubFactory_Plugin {

	protected function configure() {
		$this->slug    = 'announcement-bar';
		$this->title   = 'Announcement Bar';
		$this->version = ANNOUNCEMENT_BAR_VERSION;
	}

	protected function settings_fields() {
		return array(
			'enabled'     => array(
				'label'    => __( 'Status', 'announcement-bar' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Show the announcement bar', 'announcement-bar' ),
				'default'  => 0,
			),
			'message'     => array(
				'label'   => __( 'Message', 'announcement-bar' ),
				'type'    => 'text',
				'default' => 'Summer sale — 20% off everything this week!',
			),
			'link_text'   => array(
				'label'   => __( 'Button text', 'announcement-bar' ),
				'type'    => 'text',
				'default' => 'Shop now',
			),
			'link_url'    => array(
				'label'   => __( 'Button URL', 'announcement-bar' ),
				'type'    => 'text',
				'default' => '',
			),
			'bg'          => array(
				'label'   => __( 'Background colour', 'announcement-bar' ),
				'type'    => 'color',
				'default' => '#2271b1',
			),
			'fg'          => array(
				'label'   => __( 'Text colour', 'announcement-bar' ),
				'type'    => 'color',
				'default' => '#ffffff',
			),
			'dismissible' => array(
				'label'    => __( 'Dismiss', 'announcement-bar' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Let visitors close the bar (remembered for 7 days)', 'announcement-bar' ),
				'default'  => 1,
			),
			'sticky'      => array(
				'label'    => __( 'Sticky', 'announcement-bar' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Keep the bar fixed while scrolling', 'announcement-bar' ),
				'pro'      => true,
			),
		);
	}

	protected function hooks() {
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	public function render() {
		if ( ! $this->option( 'enabled', 0 ) || is_admin() ) {
			return;
		}
		$message = trim( (string) $this->option( 'message', '' ) );
		if ( '' === $message ) {
			return;
		}

		$bg          = $this->option( 'bg', '#2271b1' ) ?: '#2271b1';
		$fg          = $this->option( 'fg', '#ffffff' ) ?: '#ffffff';
		$dismissible = (bool) $this->option( 'dismissible', 1 );
		$sticky      = ZubFactory_Upsell::is_pro( $this->slug ) && $this->option( 'sticky', 0 );
		$link_url    = esc_url( $this->option( 'link_url', '' ) );
		$link_text   = trim( (string) $this->option( 'link_text', '' ) );
		$position    = $sticky ? 'fixed' : 'static';
		?>
		<style>
			#zab{position:<?php echo esc_attr( $position ); ?>;top:0;left:0;right:0;z-index:99990;
				background:<?php echo esc_attr( $bg ); ?>;color:<?php echo esc_attr( $fg ); ?>;
				padding:10px 44px 10px 16px;text-align:center;font-size:15px;line-height:1.4;
				font-family:inherit}
			#zab a.zab-btn{color:inherit;font-weight:600;text-decoration:underline;margin-left:8px}
			#zab .zab-x{position:absolute;right:12px;top:50%;transform:translateY(-50%);
				background:none;border:0;color:inherit;font-size:20px;cursor:pointer;line-height:1;opacity:.8}
			#zab .zab-x:hover{opacity:1}
		</style>
		<div id="zab" role="region" aria-label="<?php esc_attr_e( 'Site announcement', 'announcement-bar' ); ?>">
			<span><?php echo esc_html( $message ); ?></span>
			<?php if ( $link_url && $link_text ) : ?>
				<a class="zab-btn" href="<?php echo $link_url; // phpcs:ignore ?>"><?php echo esc_html( $link_text ); ?></a>
			<?php endif; ?>
			<?php if ( $dismissible ) : ?>
				<button class="zab-x" aria-label="<?php esc_attr_e( 'Dismiss', 'announcement-bar' ); ?>">&times;</button>
			<?php endif; ?>
		</div>
		<?php if ( $dismissible ) : ?>
		<script>
			(function(){
				var k='zab_dismissed', bar=document.getElementById('zab');
				try{
					if(localStorage.getItem(k)>Date.now()){bar.style.display='none';return;}
				}catch(e){}
				var x=bar.querySelector('.zab-x');
				if(x){x.addEventListener('click',function(){
					bar.style.display='none';
					try{localStorage.setItem(k, Date.now()+7*24*60*60*1000);}catch(e){}
				});}
			})();
		</script>
		<?php endif; ?>
		<?php
	}
}

add_action(
	'plugins_loaded',
	function () {
		( new AnnouncementBar( __FILE__ ) )->boot();
	}
);
