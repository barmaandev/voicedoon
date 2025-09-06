(function(){
	const { registerBlockType } = (window.wp && window.wp.blocks) || {};
	const { createElement: el } = (window.wp && window.wp.element) || {};
	const { InspectorControls } = (window.wp && (window.wp.blockEditor || window.wp.editor)) || {};
	const { PanelBody, TextControl, SelectControl } = (window.wp && window.wp.components) || {};
	if (!registerBlockType) return;

	const presets = [
		{ label: 'Modern', value: 'modern' },
		{ label: 'Minimal', value: 'minimal' },
		{ label: 'Neon', value: 'neon' },
		{ label: 'Wave (Continuous)', value: 'wave' },
		{ label: 'Wave Top (Half)', value: 'wave_top' },
	];

	const buttonPositions = [
		{ label: 'Outside', value: 'outside' },
		{ label: 'Inside', value: 'inside' },
	];

	registerBlockType('voice-doon/podcast-player', {
		title: 'Podcast Player',
		icon: 'format-audio',
		category: 'embed',
		attributes: {
			src: { type: 'string', default: '' },
			titleText: { type: 'string', default: '' },
			preset: { type: 'string', default: 'modern' },
			button_position: { type: 'string', default: 'outside' },
		},
		edit: function(props){
			const attrs = props.attributes;
			return el(
				'div',
				{ className: 'wbdn-voice-block-editor' },
				[
					el(InspectorControls, {},
						el(PanelBody, { title: 'Podcast Player', initialOpen: true }, [
							el(TextControl, {
								label: 'Audio URL',
								value: attrs.src,
								onChange: v => props.setAttributes({ src: v })
							}),
							el(TextControl, {
								label: 'Title (optional)',
								value: attrs.titleText,
								onChange: v => props.setAttributes({ titleText: v })
							}),
							el(SelectControl, {
								label: 'Preset',
								value: attrs.preset,
								options: presets,
								onChange: v => props.setAttributes({ preset: v })
							}),
							el(SelectControl, {
								label: 'Button position',
								value: attrs.button_position,
								options: buttonPositions,
								onChange: v => props.setAttributes({ button_position: v })
							})
						])
					),
					el('div', { className: 'wbdn-voice-block-preview' }, [
						el('p', {}, 'Voice Doon Player'),
						el('code', {}, `[voicedoon src="${attrs.src||''}" title="${attrs.titleText||''}" preset="${attrs.preset}" button_position="${attrs.button_position}"]`)
					])
				]
			);
		},
		save: function(props){
			const a = props.attributes;
			const shortcode = `[voicedoon src="${a.src||''}" title="${a.titleText||''}" preset="${a.preset}" button_position="${a.button_position}"]`;
			return el('p', {}, shortcode);
		}
	});
})();
