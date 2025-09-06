<?php
/**
 * Plugin Name: Voice Doon
 * Description: Shortcode-based audio-podcast player with a lightweight, modern canvas waveform. No external libraries.
 * Version: 1.0.0
 * Author: Barmaan Shokoohi
 * Author URI: https://webdoon.ir
 * Text Domain: voicedoon
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WBDN_Voice_Player')) {
	final class WBDN_Voice_Player {
		const VERSION = '1.0.0';
		private static $instance = null;
		private $did_register = false;

		public static function instance() {
			if (self::$instance === null) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			add_shortcode('voicedoon', array($this, 'shortcode'));
			add_action('wp_enqueue_scripts', array($this, 'register_assets'));
			add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_assets'));
			add_action('init', array($this, 'load_textdomain'));
			add_action('admin_menu', array($this, 'register_help_page'));
			add_action('admin_init', array($this, 'register_settings'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
			// Classic editor TinyMCE button
			add_action('admin_init', array($this, 'register_tinymce_button'));
		}
		public function register_tinymce_button() {
			if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
				return;
			}
			if ('true' === get_user_option('rich_editing')) {
				add_filter('mce_external_plugins', function ($plugins) {
					$plugins['wbdn_voice_button'] = plugin_dir_url(__FILE__) . 'assets/tinymce-wbdn-voice.js';
					return $plugins;
				});
				add_filter('mce_buttons', function ($buttons) {
					$buttons[] = 'wbdn_voice_button';
					return $buttons;
				});
			}
		}

		public function enqueue_block_assets() {
			$base_url = plugin_dir_url(__FILE__);
			wp_enqueue_script('wbdn-voice-block', $base_url . 'assets/js/block.js', array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-block-editor'), self::VERSION, true);
		}

		public function load_textdomain() {
			load_plugin_textdomain('voicedoon', false, dirname(plugin_basename(__FILE__)) . '/languages');
		}

		public function register_assets() {
			if ($this->did_register) {
				return;
			}
			$this->did_register = true;
			$url = plugin_dir_url(__FILE__);
			$ver = self::VERSION;
			wp_register_style('wbdn-voice-player', $url . 'assets/css/player.css', array(), $ver);
			wp_register_script('wbdn-voice-player', $url . 'assets/js/player.js', array(), $ver, true);
		}

		private function enqueue_assets() {
			$this->register_assets();
			wp_enqueue_style('wbdn-voice-player');
			wp_enqueue_script('wbdn-voice-player');
		}

		public function register_help_page() {
			add_options_page(
				__('Voice Doon', 'voicedoon'),
				__('Voice Doon', 'voicedoon'),
				'manage_options',
				'wbdn-voice-help',
				array($this, 'render_help_page')
			);
		}

		public function add_plugin_action_links($links) {
			$help_link = '<a href="' . esc_url(admin_url('options-general.php?page=wbdn-voice-help')) . '">' . esc_html__('Help', 'voicedoon') . '</a>';
			array_unshift($links, $help_link);
			return $links;
		}

		public function render_help_page() {
			// Enqueue WP color picker assets and init script for this page
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('wp-color-picker');
			wp_add_inline_script('wp-color-picker', '(function($){$(".wbdn-voice-color-field").wpColorPicker();})(jQuery);');
			// Enqueue custom admin assets
			$base_url = plugin_dir_url(__FILE__);
			wp_enqueue_style('wbdn-voice-admin', $base_url . 'assets/admin.css', array(), self::VERSION);
			wp_enqueue_script('wbdn-voice-admin', $base_url . 'assets/admin.js', array(), self::VERSION, true);
			$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';
			if (!in_array($active_tab, array('settings', 'docs'), true)) {
				$active_tab = 'settings';
			}
			?>
			<div class="wrap wbdn-voice-admin">
				<h1><?php esc_html_e('Voice Doon', 'voicedoon'); ?></h1>
				<h2 class="nav-tab-wrapper">
					<a href="<?php echo esc_url(add_query_arg(array('page' => 'wbdn-voice-help', 'tab' => 'settings'), admin_url('options-general.php'))); ?>" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Settings', 'voicedoon'); ?></a>
					<a href="<?php echo esc_url(add_query_arg(array('page' => 'wbdn-voice-help', 'tab' => 'docs'), admin_url('options-general.php'))); ?>" class="nav-tab <?php echo $active_tab === 'docs' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Documents', 'voicedoon'); ?></a>
				</h2>
				<?php if ($active_tab === 'settings'): ?>
					<div class="wbdn-voice-card">
						<h3><?php esc_html_e('Settings', 'voicedoon'); ?></h3>
						<form method="post" action="options.php">
							<?php
								settings_fields('wbdn_voice_player');
								do_settings_sections('wbdn-voice-help');
							submit_button();
							?>
						</form>
					</div>
				<?php else: ?>
					<div class="wbdn-voice-card">
						<h3><?php esc_html_e('Usage', 'voicedoon'); ?></h3>
						<p><?php esc_html_e('Use the shortcode in posts, pages, or widgets. In the block editor, add a Shortcode block.', 'voicedoon'); ?></p>
						<div class="wbdn-voice-code"><button type="button" class="wbdn-voice-copy">Copy</button><pre><code>[voicedoon src="https://example.com/audio.mp3"]</code></pre></div>
						<div class="wbdn-voice-code"><button type="button" class="wbdn-voice-copy">Copy</button><pre><code>[voicedoon src="https://example.com/audio.mp3" title="Episode 1" accent="#22c55e" progress="#0f172a" bg="#e5e7eb"]</code></pre></div>
						<div class="wbdn-voice-code"><button type="button" class="wbdn-voice-copy">Copy</button><pre><code>[voicedoon src="https://example.com/audio.mp3" height="56" preload="metadata"]</code></pre></div>
						<div class="wbdn-voice-code"><button type="button" class="wbdn-voice-copy">Copy</button><pre><code>[voicedoon src="https://example.com/audio.mp3" load_on="click"]</code></pre></div>
						<h3><?php esc_html_e('Attributes', 'voicedoon'); ?></h3>
						<ul>
							<li><strong>src</strong> — <?php esc_html_e('Audio file URL (required).', 'voicedoon'); ?></li>
							<li><strong>title</strong> — <?php esc_html_e('Optional title label.', 'voicedoon'); ?></li>
							<li><strong>accent</strong> — <?php esc_html_e('Wave bar color.', 'voicedoon'); ?></li>
							<li><strong>progress</strong> — <?php esc_html_e('Played overlay color.', 'voicedoon'); ?></li>
							<li><strong>bg</strong> — <?php esc_html_e('Canvas background color.', 'voicedoon'); ?></li>
							<li><strong>height</strong> — <?php esc_html_e('Canvas height in pixels.', 'voicedoon'); ?></li>
							<li><strong>preload</strong> — <?php esc_html_e('Audio preload attribute (none|metadata|auto).', 'voicedoon'); ?></li>
							<li><strong>load_on</strong> — <?php esc_html_e('When to load audio and waveform (view|click).', 'voicedoon'); ?></li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		public function field_preset() {
			$opts = $this->get_effective_defaults();
			?>
			<select name="wbdn_voice_player_options[preset]" id="wbdn-voice-preset-select">
				<option value="modern" <?php selected($opts['preset'], 'modern'); ?>><?php esc_html_e('Modern (Default)', 'voicedoon'); ?></option>
				<option value="minimal" <?php selected($opts['preset'], 'minimal'); ?>><?php esc_html_e('Minimal', 'voicedoon'); ?></option>
				<option value="neon" <?php selected($opts['preset'], 'neon'); ?>><?php esc_html_e('Neon', 'voicedoon'); ?></option>
				<option value="wave" <?php selected($opts['preset'], 'wave'); ?>><?php esc_html_e('Wave (Continuous)', 'voicedoon'); ?></option>
				<option value="wave_top" <?php selected($opts['preset'], 'wave_top'); ?>><?php esc_html_e('Wave Top (Half)', 'voicedoon'); ?></option>
			</select>
			<div id="wbdn-voice-preset-preview" style="margin-top: 16px;">
				<?php $this->render_preset_preview($opts['preset']); ?>
			</div>
			<?php
		}

		public function render_preset_preview($preset) {
			$presets = array(
				'modern' => array(
					'name' => 'Modern',
					'desc' => 'Clean bars with subtle shadows',
					'colors' => array('#3b82f6', '#f3f4f6', '#111827'),
					'height' => 64,
					'radius' => 2
				),
				'minimal' => array(
					'name' => 'Minimal',
					'desc' => 'Thin lines with elegant spacing',
					'colors' => array('#6b7280', '#ffffff', '#1f2937'),
					'height' => 48,
					'radius' => 0
				),
				'neon' => array(
					'name' => 'Neon',
					'desc' => 'Glowing bars with dark theme',
					'colors' => array('#10b981', '#0f172a', '#fbbf24'),
					'height' => 72,
					'radius' => 4
				),
				'wave' => array(
					'name' => 'Wave',
					'desc' => 'Continuous filled waveform without separation',
					'colors' => array('#3b82f6', '#f3f4f6', '#111827'),
					'height' => 64,
					'radius' => 0
				),
				'wave_top' => array(
					'name' => 'Wave Top',
					'desc' => 'Only the upper half of the waveform (filled to center)',
					'colors' => array('#3b82f6', '#f8fafc', '#111827'),
					'height' => 64,
					'radius' => 0
				)
			);
			$current = $presets[$preset];
			?>
			<div class="wbdn-voice-preview-card" style="border: 2px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #fff;">
				<h4 style="margin: 0 0 8px 0; color: #111827;"><?php echo esc_html($current['name']); ?></h4>
				<p style="margin: 0 0 12px 0; color: #6b7280; font-size: 14px;"><?php echo esc_html($current['desc']); ?></p>
				<div style="display: flex; gap: 8px; align-items: center;">
					<span style="font-size: 12px; color: #6b7280;">Colors:</span>
					<?php foreach ($current['colors'] as $color): ?>
						<div style="width: 20px; height: 20px; background: <?php echo esc_attr($color); ?>; border-radius: 4px; border: 1px solid #d1d5db;"></div>
					<?php endforeach; ?>
				</div>
				<div style="margin-top: 8px; font-size: 12px; color: #6b7280;">
					Height: <?php echo esc_html($current['height']); ?>px | Radius: <?php echo esc_html($current['radius']); ?>px
				</div>
			</div>
			<?php
		}

		public function field_load_on() {
			$opts = $this->get_effective_defaults();
			?>
			<select name="wbdn_voice_player_options[load_on]">
				<option value="view" <?php selected($opts['load_on'], 'view'); ?>><?php esc_html_e('On View', 'voicedoon'); ?></option>
				<option value="click" <?php selected($opts['load_on'], 'click'); ?>><?php esc_html_e('On Click', 'voicedoon'); ?></option>
			</select>
			<?php
		}

		public function field_preload() {
			$opts = $this->get_effective_defaults();
			?>
			<select name="wbdn_voice_player_options[preload]">
				<option value="none" <?php selected($opts['preload'], 'none'); ?>>none</option>
				<option value="metadata" <?php selected($opts['preload'], 'metadata'); ?>>metadata</option>
				<option value="auto" <?php selected($opts['preload'], 'auto'); ?>>auto</option>
			</select>
			<?php
		}

		public function field_height() {
			$opts = $this->get_effective_defaults();
			?>
			<input type="number" min="16" step="1" name="wbdn_voice_player_options[height]" value="<?php echo esc_attr($opts['height']); ?>" />
			<?php
		}

		public function field_radius() {
			$opts = $this->get_effective_defaults();
			?>
			<input type="number" min="0" step="1" name="wbdn_voice_player_options[radius]" value="<?php echo esc_attr($opts['radius']); ?>" />
			<?php
		}

		public function field_accent() {
			$opts = $this->get_effective_defaults();
			?>
			<input type="text" class="regular-text wbdn-voice-color-field" name="wbdn_voice_player_options[accent]" value="<?php echo esc_attr($opts['accent']); ?>" />
			<?php
		}

		public function field_progress() {
			$opts = $this->get_effective_defaults();
			?>
			<input type="text" class="regular-text wbdn-voice-color-field" name="wbdn_voice_player_options[progress]" value="<?php echo esc_attr($opts['progress']); ?>" />
			<?php
		}

		public function field_bg() {
			$opts = $this->get_effective_defaults();
			?>
			<input type="text" class="regular-text wbdn-voice-color-field" name="wbdn_voice_player_options[bg]" value="<?php echo esc_attr($opts['bg']); ?>" />
			<?php
		}

		public function field_button_position() {
			$opts = $this->get_effective_defaults();
			?>
			<select name="wbdn_voice_player_options[button_position]">
				<option value="outside" <?php selected($opts['button_position'], 'outside'); ?>><?php esc_html_e('Outside (left of waveform)', 'voicedoon'); ?></option>
				<option value="inside" <?php selected($opts['button_position'], 'inside'); ?>><?php esc_html_e('Inside (within waveform area)', 'voicedoon'); ?></option>
			</select>
			<?php
		}

		public function field_wave_style() {
			$opts = $this->get_effective_defaults();
			?>
			<select name="wbdn_voice_player_options[wave_style]" id="wbdn-voice-wave-style-select">
				<option value="bars" <?php selected($opts['wave_style'], 'bars'); ?>><?php esc_html_e('Bars', 'voicedoon'); ?></option>
				<option value="line" <?php selected($opts['wave_style'], 'line'); ?>><?php esc_html_e('Line', 'voicedoon'); ?></option>
				<option value="continuous" <?php selected($opts['wave_style'], 'continuous'); ?>><?php esc_html_e('Continuous (Filled)', 'voicedoon'); ?></option>
			</select>
			<?php
		}

		public function shortcode($atts = array(), $content = '') {
			$defaults = $this->get_effective_defaults();
			// Keep original user-provided attributes to detect explicit overrides
			$user_atts = is_array($atts) ? $atts : array();
			$atts = shortcode_atts(array(
				'src' => '',
				'title' => '',
				'accent' => $defaults['accent'],
				'bg' => $defaults['bg'],
				'progress' => $defaults['progress'],
				'height' => (string) $defaults['height'],
				'preload' => $defaults['preload'],
				'load_on' => $defaults['load_on'],
				'radius' => (string) $defaults['radius'],
				'preset' => $defaults['preset'],
				'wave_style' => $defaults['wave_style'],
				'button_position' => $defaults['button_position'],
			), $atts, 'voicedoon');

			// Apply preset defaults if user selected a preset and did not override specific fields
			$preset = isset($atts['preset']) ? sanitize_key($atts['preset']) : 'modern';
			$presets = array(
				'modern' => array(
					'accent' => '#3b82f6', 'bg' => '#f3f4f6', 'progress' => '#111827', 'height' => 64, 'radius' => 2, 'wave_style' => 'bars',
				),
				'minimal' => array(
					'accent' => '#6b7280', 'bg' => '#ffffff', 'progress' => '#1f2937', 'height' => 48, 'radius' => 0, 'wave_style' => 'line',
				),
				'neon' => array(
					'accent' => '#10b981', 'bg' => '#0f172a', 'progress' => '#fbbf24', 'height' => 72, 'radius' => 4, 'wave_style' => 'bars',
				),
				'wave' => array(
					'accent' => '#3b82f6', 'bg' => '#f3f4f6', 'progress' => '#111827', 'height' => 64, 'radius' => 0, 'wave_style' => 'continuous',
				),
				'wave_top' => array(
					'accent' => '#3b82f6', 'bg' => '#f8fafc', 'progress' => '#111827', 'height' => 64, 'radius' => 0, 'wave_style' => 'continuous',
				),
			);
			if (isset($presets[$preset])) {
				$p = $presets[$preset];
				if (!isset($user_atts['accent']) || $user_atts['accent'] === '') $atts['accent'] = $p['accent'];
				if (!isset($user_atts['bg']) || $user_atts['bg'] === '') $atts['bg'] = $p['bg'];
				if (!isset($user_atts['progress']) || $user_atts['progress'] === '') $atts['progress'] = $p['progress'];
				if (!isset($user_atts['height']) || $user_atts['height'] === '') $atts['height'] = (string) $p['height'];
				if (!isset($user_atts['radius']) || $user_atts['radius'] === '') $atts['radius'] = (string) $p['radius'];
				if (!isset($user_atts['wave_style']) || $user_atts['wave_style'] === '') $atts['wave_style'] = $p['wave_style'];
			}

			if (empty($atts['src'])) {
				return '';
			}

			$this->enqueue_assets();

			$id = 'wbdn-voice-' . wp_generate_uuid4();
			$button_position = ($atts['button_position'] === 'inside') ? 'inside' : 'outside';
			$root_classes = 'wbdn-voice-player wbdn-voice-preset-' . esc_attr($atts['preset']) . ' wbdn-voice-btn-' . esc_attr($button_position);
			ob_start();
			?>
			<div class="<?php echo $root_classes; ?>" id="<?php echo esc_attr($id); ?>"
					 data-src="<?php echo esc_url($atts['src']); ?>"
					 data-title="<?php echo esc_attr($atts['title']); ?>"
					 data-accent="<?php echo esc_attr($atts['accent']); ?>"
					 data-bg="<?php echo esc_attr($atts['bg']); ?>"
					 data-progress="<?php echo esc_attr($atts['progress']); ?>"
					 data-height="<?php echo esc_attr($atts['height']); ?>"
					 data-preload="<?php echo esc_attr($atts['preload']); ?>"
					 data-load-on="<?php echo esc_attr($atts['load_on']); ?>"
					 data-radius="<?php echo esc_attr($atts['radius']); ?>"
					 data-preset="<?php echo esc_attr($atts['preset']); ?>"
					 data-wave-style="<?php echo esc_attr($atts['wave_style']); ?>">
				<?php if ($button_position === 'outside'): ?>
					<button class="wbdn-voice-button" aria-label="Play"><span class="wbdn-voice-icon">▶</span></button>
				<?php endif; ?>
				<div class="wbdn-voice-wave-wrap">
					<?php if ($button_position === 'inside'): ?>
						<button class="wbdn-voice-button" aria-label="Play"><span class="wbdn-voice-icon">▶</span></button>
					<?php endif; ?>
					<canvas class="wbdn-voice-wave"></canvas>
					<div class="wbdn-voice-time">
						<span class="wbdn-voice-current">0:00</span>
						<span class="wbdn-voice-duration">--:--</span>
					</div>
				</div>
				<?php if (!empty($atts['title'])): ?>
					<div class="wbdn-voice-title"><?php echo esc_html($atts['title']); ?></div>
				<?php endif; ?>
				<noscript><audio controls src="<?php echo esc_url($atts['src']); ?>"></audio></noscript>
			</div>
			<?php
			return ob_get_clean();
		}

		private function get_default_config() {
			return array(
				'accent' => '#3b82f6',
				'bg' => '#f3f4f6',
				'progress' => '#111827',
				'height' => 64,
				'preload' => 'none',
				'load_on' => 'view',
				'radius' => 2,
				'preset' => 'modern',
				'wave_style' => 'bars',
				'button_position' => 'outside',
			);
		}

		private function get_effective_defaults() {
			$stored = get_option('wbdn_voice_player_options', array());
			
			// Migration: Check for old option name and migrate if needed
			if (empty($stored)) {
				$old_options = get_option('cpp_player_options', array());
				if (!empty($old_options)) {
					update_option('wbdn_voice_player_options', $old_options);
					delete_option('cpp_player_options'); // Clean up old option
					$stored = $old_options;
				}
			}
			
			$defaults = $this->get_default_config();
			return wp_parse_args(is_array($stored) ? $stored : array(), $defaults);
		}

		public function register_settings() {
			register_setting('wbdn_voice_player', 'wbdn_voice_player_options', array(
				'type' => 'array',
				'sanitize_callback' => array($this, 'sanitize_options'),
				'default' => $this->get_default_config(),
			));

			add_settings_section('wbdn_voice_player_main', __('Default Settings', 'voicedoon'), function () {
				echo '<p>' . esc_html__('Configure default appearance and behavior. Shortcode attributes override these.', 'voicedoon') . '</p>';
			}, 'wbdn-voice-help');

			add_settings_field('wbdn_voice_preset', __('Design Preset', 'voicedoon'), array($this, 'field_preset'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_wave_style', __('Wave Style', 'voicedoon'), array($this, 'field_wave_style'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_button_position', __('Button position', 'voicedoon'), array($this, 'field_button_position'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_load_on', __('Load on', 'voicedoon'), array($this, 'field_load_on'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_preload', __('Preload', 'voicedoon'), array($this, 'field_preload'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_height', __('Wave height (px)', 'voicedoon'), array($this, 'field_height'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_radius', __('Bar radius (px)', 'voicedoon'), array($this, 'field_radius'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_accent', __('Accent color', 'voicedoon'), array($this, 'field_accent'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_progress', __('Progress color', 'voicedoon'), array($this, 'field_progress'), 'wbdn-voice-help', 'wbdn_voice_player_main');
			add_settings_field('wbdn_voice_bg', __('Background color', 'voicedoon'), array($this, 'field_bg'), 'wbdn-voice-help', 'wbdn_voice_player_main');
		}

		public function sanitize_options($input) {
			$defaults = $this->get_default_config();
			$out = array();
			$preset = isset($input['preset']) ? sanitize_key($input['preset']) : $defaults['preset'];
			$out['preset'] = in_array($preset, array('modern', 'minimal', 'neon', 'wave', 'wave_top'), true) ? $preset : $defaults['preset'];
			$wave_style = isset($input['wave_style']) ? sanitize_key($input['wave_style']) : $defaults['wave_style'];
			$out['wave_style'] = in_array($wave_style, array('bars', 'line', 'continuous'), true) ? $wave_style : $defaults['wave_style'];
			$btn_pos = isset($input['button_position']) ? sanitize_key($input['button_position']) : $defaults['button_position'];
			$out['button_position'] = in_array($btn_pos, array('inside', 'outside'), true) ? $btn_pos : $defaults['button_position'];
			$load_on = isset($input['load_on']) ? strtolower($input['load_on']) : $defaults['load_on'];
			$out['load_on'] = in_array($load_on, array('view', 'click'), true) ? $load_on : $defaults['load_on'];
			$preload = isset($input['preload']) ? strtolower($input['preload']) : $defaults['preload'];
			$out['preload'] = in_array($preload, array('none', 'metadata', 'auto'), true) ? $preload : $defaults['preload'];
			$out['height'] = max(16, intval(isset($input['height']) ? $input['height'] : $defaults['height']));
			$out['radius'] = max(0, intval(isset($input['radius']) ? $input['radius'] : $defaults['radius']));
			$out['accent'] = sanitize_hex_color(isset($input['accent']) ? $input['accent'] : $defaults['accent']);
			if (empty($out['accent'])) $out['accent'] = $defaults['accent'];
			$out['progress'] = sanitize_hex_color(isset($input['progress']) ? $input['progress'] : $defaults['progress']);
			if (empty($out['progress'])) $out['progress'] = $defaults['progress'];
			$out['bg'] = sanitize_hex_color(isset($input['bg']) ? $input['bg'] : $defaults['bg']);
			if (empty($out['bg'])) $out['bg'] = $defaults['bg'];
			return $out;
		}
	}

	WBDN_Voice_Player::instance();
}


