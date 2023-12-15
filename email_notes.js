

window.rcmail && rcmail.addEventListener('init', function(evt) {
	$('.email_note .edit').click(function() {
		const $container = $(this).closest('.email_note');

		console.log($container.find('.content'));
		const $content = $container.find('.content > .text');

		$container.data('orig-content', $content.text());

		
		let $replace = $('<div class="editor" />');
		$replace.append($('<textarea name="content" rows="5" cols="40"></textarea>').val($content.text()));
		$replace.append($('<button class="save btn btn-sm btn-primary">'+rcmail.gettext('save')+'</button>'));
		$replace.append($('<button class="cancel btn btn-sm btn-danger">'+rcmail.gettext('cancel')+'</button>'));

		$content.replaceWith($replace);
	});

	$('.email_note').on('click', '.editor .save', function() {
		const $container = $(this).closest('.email_note');

		const $content = $container.find('.editor textarea[name="content"]');
		const content = $content.val();

		console.log($container.attr('data-message-id'));
		window.rcmail.http_post('plugin.email_notes.save_note', {
			message_id: $container.attr('data-message-id'),
			note: content,
		});


		$container.find('.editor').replaceWith($('<div class="text" />').text(content));
	});

	$('.email_note').on('click', '.editor .cancel', function() {
		const $container = $(this).closest('.email_note');

		$container.find('.editor').replaceWith($('<div class="text" />').text($container.data('orig-content')));

	});
});
