import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import './editor.scss'; // We will create this or use css
import Edit from './edit';
import metadata from './block.json';

registerBlockType(metadata.name, {
    edit: Edit,
    save: () => null, // Dynamic block
});
