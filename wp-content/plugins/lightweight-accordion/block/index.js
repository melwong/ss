{
const {registerBlockType} = wp.blocks;
const {createElement,Component} = wp.element;
const {__} = wp.i18n;
const {InspectorControls,PanelColorSettings,RichText,BlockControls,InnerBlocks,AlignmentToolbar} = wp.blockEditor;
const {TextControl,ToggleControl,SelectControl,Panel,PanelBody,PanelRow,ColorIndicator} = wp.components;

registerBlockType( 'lightweight-accordion/lightweight-accordion', {
	title: __( 'Lightweight Accordion' ),
	category:  __( 'widgets' ),
	icon: 'editor-ul',
	keywords: [
		__( 'accordion' ),
		__( 'list' ),
		__( 'collapse' ),
		__( 'collapsable' ),
	],
	supports: {
		  anchor: true
	},
	attributes:  {
		content: {
			type: 'array',
			source: 'children'
		},
		anchor: {
			type: 'string',
			default: null
		},
		title: {
			type: 'string',
			default: null
		},
		title_tag: {
			type: 'string',
			default: 'span'
		},
		title_text_color: {
			type: 'string',
			default: false
		},
		title_background_color: {
			type: 'string',
			default: false
		},
		accordion_open: {
			type: 'boolean',
			default: false
		},
		bordered: {
			type: 'boolean',
			default: false
		},
		schema: {
			type: 'string',
			default: false
		}
	},

	example: {
		attributes: {
			title: __('Accordion Title'),
			bordered: true,
		},
	},

	edit: function( props ) {
		const attributes =  props.attributes;
		const setAttributes =  props.setAttributes;
		var content = props.attributes.content;
		function changeContent(newContent){
			setAttributes({content: newContent});
		}
		function changeTitle(title){
			setAttributes({title});
		}
		function changeTitleTag(title_tag){
			setAttributes({title_tag});
		}
		function changeTitleTextColor(title_text_color){
			setAttributes({title_text_color});
		}
		function changeTitleBackgroundColor(title_background_color){
			setAttributes({title_background_color});
		}
		function changeSchema(schema){
			setAttributes({schema});
		}
		function changeAccordionOpen(accordion_open){
			setAttributes({accordion_open});
		}
		function changeBordered(bordered){
			setAttributes({bordered});
		}
		
		if(attributes.bordered){
			setAttributes({borderedClass: 'bordered'});
		}else{
			setAttributes({borderedClass: ''});
		}
		
		if(!attributes.title_background_color){
			attributes.title_background_color = null;
		}
		if(!attributes.title_text_color){
			attributes.title_text_color = null;
		}
		
		return createElement('div', {className: 'lightweight-accordion ' + attributes.borderedClass,id: attributes.anchor}, [
			createElement(BlockControls,
            {
                key: 'controls',
            }),
			createElement("summary",
			{
				className: 'lightweight-accordion-title ' + props.className,
				style: {
					color: attributes.title_text_color,
					background: attributes.title_background_color
				},
				
			}, createElement(RichText,
            {
				tagName: 'span',
                role: 'textbox',
                onChange: changeTitle,
                value: attributes.title,
                keepPlaceholderOnFocus: true,
                placeholder: __("Accordion title..."),
                allowedFormats: ["core/bold", "core/italic"],
            })),
            
			createElement("div",
            {
                className: 'lightweight-accordion-body ' + props.className,
				style: {
					borderColor: attributes.title_background_color
				},
            },createElement(InnerBlocks)),

			//Block inspector
			createElement( InspectorControls, {},
				createElement( PanelBody, {},
					[
						createElement(ToggleControl, {
							checked: attributes.accordion_open,
							label: __( 'Open by default?' ),
							onChange: changeAccordionOpen
						}),
						createElement(ToggleControl, {
							checked: attributes.bordered,
							label: __( 'Border?' ),
							onChange: changeBordered
						}),
						createElement(SelectControl, {
							value: attributes.title_tag,
							label: __( 'HTML tag for accordion title' ),
							onChange: changeTitleTag,
							options: [
								{value: 'span', label: 'span'},
								{value: 'div', label: 'div'},
								{value: 'p', label: 'p'},
								{value: 'h1', label: 'H1'},
								{value: 'h2', label: 'H2'},
								{value: 'h3', label: 'H3'},
								{value: 'h4', label: 'H4'},
							]
						}),
						createElement(SelectControl, {
							value: attributes.schema,
							label: __( 'Schema Markup?' ),
							onChange: changeSchema,
							options: [
								{value: false, label: 'None'},
								{value: 'faq', label: 'FAQ'},
							]
						})
					]
				),
				createElement(PanelColorSettings, {
					title: __('Color Settings'),
					colorSettings: [{
					value: attributes.title_text_color,
					onChange: changeTitleTextColor,
					label: __('Title Text Color')
					},
					{
					value: attributes.title_background_color,
					onChange: changeTitleBackgroundColor,
					label: __('Title Background Color')
					}]
				})
			)
		] )
	},
	save: function( props ) {
		return createElement(
			InnerBlocks.Content,
			{ 
				className: props.className,
				value: props.attributes.content
			}
		);
	},
});
}