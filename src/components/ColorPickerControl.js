import { BaseControl, ColorPicker } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';

/**
 * A custom ColorPicker control to standardize color selection
 * across Jankx custom blocks.
 */
const ColorPickerControl = ( {
	label,
	color,
	onChange,
	help,
	className = '',
} ) => {
	const instanceId = useInstanceId( ColorPickerControl );
	const id = `jankx-color-picker-control-${ instanceId }`;

	return (
		<BaseControl
			id={ id }
			label={ label || __( 'Color', 'jankx' ) }
			help={ help }
			className={ `jankx-gutenberg-control jankx-color-picker ${ className }` }
		>
			<ColorPicker
				color={ color }
				onChangeComplete={ ( value ) => onChange( value.hex ) }
				disableAlpha
			/>
		</BaseControl>
	);
};

export default ColorPickerControl;
