( function ( blocks, element ) {
	var el = element.createElement;

	blocks.registerBlockType( 'direction-manual/embed', {
		apiVersion: 2,
		title: 'Direction Manual',
		description: 'Embed the photography Direction Manual.',
		icon: 'camera',
		category: 'embed',
		supports: { html: false, align: [ 'wide', 'full' ] },
		edit: function () {
			return el(
				'div',
				{
					style: {
						border: '1px dashed #8a8f94',
						borderRadius: '4px',
						padding: '28px',
						textAlign: 'center',
						fontFamily: 'system-ui, sans-serif',
						color: '#50575e',
						background: '#f6f7f7'
					}
				},
				el( 'strong', {}, '📷 Direction Manual' ),
				el( 'div', { style: { marginTop: '6px', fontSize: '13px' } },
					'The 108-volume manual renders here on the front end.' )
			);
		},
		// Dynamic block: server renders it via PHP render_callback.
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element );
