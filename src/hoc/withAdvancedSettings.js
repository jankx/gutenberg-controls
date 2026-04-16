import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * A Higher-Order Component to automatically append advanced
 * setting toggles to existing blocks.
 */
const withAdvancedSettings = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { attributes, setAttributes, isSelected } = props;
		
		return (
			<>
				<BlockEdit { ...props } />
				
				{ isSelected && (
					<InspectorControls>
						<PanelBody
							title={ __( 'Jankx Advanced Settings', 'jankx' ) }
							initialOpen={ false }
						>
							{/* Example Boolean Setting automatically mixed in */}
							<ToggleControl
								label={ __( 'Enable Custom Animation', 'jankx' ) }
								checked={ !! attributes.jankxEnableAnimation }
								onChange={ ( val ) => setAttributes( { jankxEnableAnimation: val } ) }
							/>
						</PanelBody>
					</InspectorControls>
				) }
			</>
		);
	};
}, 'withAdvancedSettings' );

export default withAdvancedSettings;
