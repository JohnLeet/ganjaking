/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { useCallback, useRef } from '@wordpress/element';
import classNames from 'classnames';
import Downshift from 'downshift';

/**
 * Internal dependencies
 */
import DropdownSelectorInput from './input';
import DropdownSelectorInputWrapper from './input-wrapper';
import DropdownSelectorMenu from './menu';
import DropdownSelectorSelectedChip from './selected-chip';
import DropdownSelectorSelectedValue from './selected-value';

/**
 * Component used to show an input box with a dropdown with suggestions.
 *
 * @param {Object} props Incoming props for the component.
 * @param {string} props.placeholder Label for the attributes.
 * @param {string} props.className CSS class used.
 * @param {Array} props.checked Which items are checked.
 * @param {string} props.inputLabel Label used for the input.
 * @param {boolean} props.isDisabled Whether the input is disabled or not.
 * @param {boolean} props.isLoading Whether the input is loading.
 * @param {boolean} props.multiple Whether multi-select is allowed.
 * @param {function( string ):any} props.onChange Function to be called when onChange event fires.
 * @param {Array} props.options The option values to show in the select.
 * @param {function():any} props.handleChange sets state for search data.
 */
const DropdownSelector = ( {
	placeholder = '',
	className,
	checked = [],
	inputLabel = '',
	isDisabled = false,
	isLoading = false,
	multiple = false,
	onChange = () => {},
	options = [],
	handleChange = () => {},
} ) => {
	const inputRef = useRef( null );

	const classes = classNames(
		className,
		'wc-block-dropdown-selector',
		'wc-block-components-dropdown-selector',
		{
			'is-disabled': isDisabled,
			'is-loading': isLoading,
		}
	);

	/**
	 * State reducer for the downshift component.
	 * See: https://github.com/downshift-js/downshift#statereducer
	 */
	const stateReducer = useCallback(
		( state, changes ) => {
			switch ( changes.type ) {
				case Downshift.stateChangeTypes.keyDownEnter:
				case Downshift.stateChangeTypes.clickItem:
					return {
						...changes,
						highlightedIndex: state.highlightedIndex,
						isOpen: multiple,
						inputValue: '',
					};
				case Downshift.stateChangeTypes.blurInput:
				case Downshift.stateChangeTypes.mouseUp:
					return {
						...changes,
						inputValue: state.inputValue,
					};
				default:
					return changes;
			}
		},
		[ multiple ]
	);

	return (
		<Downshift
			onChange={ onChange }
			selectedItem={ null }
			stateReducer={ stateReducer }
		>
			{ ( {
				getInputProps,
				getItemProps,
				getLabelProps,
				getMenuProps,
				highlightedIndex,
				inputValue,
				isOpen,
				openMenu,
			} ) => (
				<div
					className={ classNames( classes, {
						'is-multiple': multiple,
						'is-single': ! multiple,
						'has-checked': checked.length > 0,
						'is-open': isOpen,
					} ) }
				>
					{ /* eslint-disable-next-line jsx-a11y/label-has-for */ }
					<label
						{ ...getLabelProps( {
							className: 'screen-reader-text',
						} ) }
					>
						{ inputLabel }
					</label>
					<DropdownSelectorInputWrapper
						isOpen={ isOpen }
						onClick={ () => {
							inputRef.current.focus();
						} }
					>
						{ checked.map( ( option ) => {
							const onRemoveItem = ( val ) => {
								onChange( val );
								inputRef.current.focus();
							};
							return (
								option &&
								( multiple ? (
									<DropdownSelectorSelectedChip
										key={ option.value }
										onRemoveItem={ onRemoveItem }
										option={ option }
									/>
								) : (
									<DropdownSelectorSelectedValue
										key={
											option
												? option.value
												: Math.random()
										}
										onClick={ () => {
											inputRef.current.focus();
										} }
										onRemoveItem={ onRemoveItem }
										option={ option }
									/>
								) )
							);
						} ) }
						<DropdownSelectorInput
							checked={ checked }
							getInputProps={ getInputProps }
							inputRef={ inputRef }
							isDisabled={ isDisabled }
							onFocus={ () => {
								openMenu();
							} }
							onRemoveItem={ ( val ) => {
								onChange( val );
								inputRef.current.focus();
							} }
							placeholder={
								checked.length > 0 && multiple
									? null
									: placeholder
							}
							tabIndex={
								// When it's a single selector and there is one element selected,
								// we make the input non-focusable with the keyboard because it's
								// visually hidden. The input is still rendered, though, because it
								// must be possible to focus it when pressing the select value chip.
								! multiple && checked.length > 0 ? '-1' : '0'
							}
							value={ inputValue }
							onType={ handleChange }
						/>
					</DropdownSelectorInputWrapper>

					{ isOpen && ! isDisabled && (
						<DropdownSelectorMenu
							checked={ checked }
							getItemProps={ getItemProps }
							getMenuProps={ getMenuProps }
							highlightedIndex={ highlightedIndex }
							options={ options.filter( ( option ) => {
								const label = option.label.toLowerCase();
								return (
									! inputValue ||
									label.includes( inputValue.toLowerCase() )
								);
							} ) }
						/>
					) }
				</div>
			) }
		</Downshift>
	);
};

DropdownSelector.propTypes = {
	placeholder: PropTypes.string,
	checked: PropTypes.array,
	className: PropTypes.string,
	inputLabel: PropTypes.string,
	isDisabled: PropTypes.bool,
	isLoading: PropTypes.bool,
	limit: PropTypes.number,
	onChange: PropTypes.func,
	options: PropTypes.arrayOf(
		PropTypes.shape( {
			label: PropTypes.node.isRequired,
			value: PropTypes.oneOfType( [
				PropTypes.string.isRequired,
				PropTypes.number.isRequired,
			] ),
		} )
	),
	search: PropTypes.func,
	loadDropdown: PropTypes.func,
};

export default DropdownSelector;
