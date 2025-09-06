=== Voice Doon ===
Contributors: webdoon
Author: Barmaan Shokoohi
Author URI: https://webdoon.ir
Tags: audio, podcast, player, waveform, canvas, lightweight, persian, farsi
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, modern audio player with a canvas waveform. No external libraries. Fast, clean, and customizable. Supports Persian/Farsi language.

== Description ==
Voice Doon is a lightweight podcast/audio player that renders a beautiful waveform using HTML5 Canvas — with zero external libraries. It's modern, fast, and easy to customize.

**Key Features:**
- Lightweight: no jQuery, no external dependencies
- Modern: canvas-based waveforms with multiple presets (modern, minimal, neon, wave, wave top)
- Flexible: choose button position (inside/outside), colors, sizes
- Privacy-friendly: no third-party calls for rendering
- Shortcode-based: works in posts, pages, and widgets
- Multilingual: supports Persian/Farsi translations
- WordPress Block Editor: includes Gutenberg block support
- TinyMCE Integration: button in classic editor

**Design Presets:**
- Modern: Clean bars with subtle shadows
- Minimal: Thin lines with elegant spacing
- Neon: Glowing bars with dark theme
- Wave: Continuous filled waveform
- Wave Top: Only upper half of waveform

**Customization Options:**
- Wave style: Bars, Line, or Continuous
- Button position: Inside or outside waveform
- Colors: Accent, progress, and background
- Dimensions: Height and border radius
- Loading behavior: On view or on click
- Audio preload: None, metadata, or auto

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate "Voice Doon" from Plugins in WordPress
3. Go to Settings → Voice Doon to configure defaults
4. Add the shortcode to a post or page

== Usage ==

**Basic Usage:**
```
[podcast_player src="https://example.com/audio.mp3"]
```

**With Custom Options:**
```
[podcast_player src="https://example.com/audio.mp3" title="Episode 1" preset="wave" button_position="inside" accent="#3b82f6" bg="#f3f4f6" progress="#111827"]
```

**All Available Attributes:**
- `src` - Audio file URL (required)
- `title` - Optional title label
- `preset` - Design preset (modern, minimal, neon, wave, wave_top)
- `wave_style` - Wave style (bars, line, continuous)
- `button_position` - Button position (inside, outside)
- `accent` - Wave bar color (hex color)
- `progress` - Played overlay color (hex color)
- `bg` - Canvas background color (hex color)
- `height` - Canvas height in pixels
- `radius` - Bar radius in pixels
- `preload` - Audio preload attribute (none, metadata, auto)
- `load_on` - When to load audio (view, click)

**Block Editor:**
In the Gutenberg editor, add a "Shortcode" block and use the shortcode above.

**Classic Editor:**
Use the "Voice Doon" button in the TinyMCE toolbar to insert the shortcode.

== Frequently Asked Questions ==

= Does it load external libraries? =
No. The player uses native web APIs and HTML5 Canvas. No jQuery or external dependencies.

= Can I customize the look? =
Yes. Choose from 5 presets, customize colors, sizes, and button position. You can also override CSS classes.

= Does it support Persian/Farsi? =
Yes. The plugin includes Persian translations and RTL support.

= How do I check WordPress Coding Standards (WPCS) compliance? =
Install PHPCS and WPCS and run the ruleset included with the plugin:

1) Install tools (once):
```bash
composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs
```

2) Point PHPCS to WPCS (one-time):
```bash
~/.composer/vendor/bin/phpcs --config-set installed_paths ~/.composer/vendor/wpcs
```

3) Run on this plugin directory:
```bash
~/.composer/vendor/bin/phpcs --standard=phpcs.xml.dist /path/to/voicedoon
```

= How do I contribute? =
This plugin is developed by WebDoon. For issues and contributions, please visit our website at https://webdoon.ir

== Screenshots ==
1. Settings page with design presets
2. Player with modern preset
3. Player with minimal preset
4. Player with neon preset
5. Player with wave preset
6. Block editor integration

== Frequently Asked Questions ==

== Changelog ==

= 1.0.0 =
* Initial release
* Canvas-based waveform rendering
* 5 design presets (modern, minimal, neon, wave, wave_top)
* 3 wave styles (bars, line, continuous)
* Customizable colors and dimensions
* Shortcode support
* Gutenberg block integration
* TinyMCE button
* Persian/Farsi translations
* WordPress Coding Standards compliance
* No external dependencies

== Upgrade Notice ==

= 1.0.0 =
Initial release of Voice Doon. Install to get a lightweight, modern audio player with canvas waveforms.