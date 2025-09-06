(function(){
	if (!window.tinymce) return;
	tinymce.PluginManager.add('wbdn_voice_button', function(editor){
		editor.addButton('wbdn_voice_button', {
			text: 'Podcast',
			icon: 'media',
			onclick: function(){
				var src = prompt('Audio URL');
				if (!src) return;
				var title = prompt('Title (optional)') || '';
				var preset = prompt('Preset (modern|minimal|neon|wave|wave_top)', 'modern') || 'modern';
				var pos = prompt('Button position (outside|inside)', 'outside') || 'outside';
				editor.insertContent('[podcast_player src="'+src+'" title="'+title+'" preset="'+preset+'" button_position="'+pos+'"]');
			}
		});
	});
})();
