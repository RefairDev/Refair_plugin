<?php
/**
 * The admin-partials view of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin\Metas\Views;

use Refairplugin\Refairplugin_Meta_View;
use Refairplugin\Metas\Refairplugin_Utils;

/**
 * Class managing extensible meta view display and saving.
 */
class Refairplugin_Extensible_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'extensible';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
	}

	/**
	 * Get html content of the meta box.
	 *
	 * @param  string $view_content Previous content of the view of post meta boxes.
	 * @param  array  $data Data to use to generate metabox content.
	 * @param  mixed  $value Value to set to metabox inputs.
	 * @return string Content of view added with the current metabox.
	 */
	public function get_view( $view_content, $data, $value = null ) {

		$extensible_view = $view_content;
		if ( isset( $data['options']['default'] ) ) {
			$child_defaults_values = $data['options']['default'];}

		if ( is_array( $value ) ) {
			$value_count = count( $value );
		} elseif ( isset( $child_defaults_values ) ) {
			$value_count = count( $child_defaults_values );
		} else {
			$value_count = 1;
		}

		$child_meta = $data['options']['meta'];

		$child_meta_obj = new \ArrayObject( $child_meta );
		$child_meta_arr = $child_meta_obj->getArrayCopy();
		$meta_name      = $data['options']['meta']->name;

		ob_start();
		?>
		<p>
			<label for="<?php echo esc_attr( $data['id'] ); ?>-input-number"></label><input id="<?php echo esc_attr( $data['id'] ); ?>-input-number" type=number min=1 value="<?php echo wp_kses_post( $value_count ); ?>">
		</p>
		<div id="<?php echo esc_attr( $data['id'] . '_extensible' ); ?>">
			<?php
			for ( $idx = 0; $idx < $value_count; $idx++ ) :

				$meta_value = null;
				if ( isset( $value[ $idx ] ) ) {
					$meta_value = $value[ $idx ];
				} elseif ( isset( $child_defaults_values ) && is_array( $child_defaults_values ) && isset( $child_defaults_values[ $idx ] ) ) {
					$meta_value = $child_defaults_values[ $idx ];
				}
				$child_meta_arr['meta_name'] = $meta_name;
				$child_meta_arr['name']      = $data['name'] . '[' . $idx . ']';
				$child_meta_arr['id']        = $data['id'] . '-' . $idx;
				?>
			<div id="<?php echo esc_attr( $data['id'] . '-' . $idx ); ?>_block">
				<label for="<?php echo esc_attr( $data['id'] . '-' . $idx ); ?>"><?php echo wp_kses_post( ( $idx + 1 ) . '. ' ); ?></label>
				<?php echo wp_kses( apply_filters( 'refairplugin_renderview_' . $child_meta_arr['type'], $view_content, $child_meta_arr, $meta_value ), wp_kses_allowed_html( 'strip' ) ); ?>                
			</div>
			<?php endfor; ?>
			
		</div>
		
		<script>

			var inputNumber = document.getElementById("<?php echo esc_attr( $data['id'] ); ?>-input-number");
			inputNumber.onchange = changeInputNumber;
			var newKeyIdx = 0;

			function extensible<?php echo esc_attr( $data['meta_name'] ); ?>Script(){
				<?php $meta = $data['options']['meta']; ?>
					if (typeof <?php echo esc_attr( $meta->type . $meta->name ); ?>Script == 'function'){<?php echo esc_attr( $meta->type . $meta->name ); ?>Script()};
			}
	
			function changeInputNumber(event){
	
				var newInputNumber = event.target.value;
				var inputBlock = document.getElementById("<?php echo esc_attr( $data['id'] . '_extensible' ); ?>");
	
				if(inputBlock.children.length < newInputNumber)
				{
					while (inputBlock.children.length < newInputNumber){
						var newKey = inputBlock.firstElementChild.cloneNode(true);
						newKeyIdx = (parseInt(inputBlock.children.length)).toString();
						newKey.id = newKey.id.replace("-0","-"+newKeyIdx);
						var children = newKey.childNodes;
	
						children.forEach(replaceIdx);
						inputBlock.appendChild(newKey);
					}

					extensible<?php echo esc_attr( $data['meta_name'] ); ?>Script();
				
				}else{
					while (inputBlock.children.length > newInputNumber){
						inputBlock.removeChild(inputBlock.lastElementChild);
					}
				}
			}

			function replaceIdx(currentNode, currentIndex, nodesList){
				attrs = ["htmlFor","id","name","value"];
				attrs.forEach(function(item){
					if(currentNode[item]){
						currentNode[item] = currentNode[item].replace("[0]","["+newKeyIdx+"]");
						currentNode[item] = currentNode[item].replace("-0","-"+newKeyIdx);
						if (item=="htmlFor"){ currentNode.innerText = currentNode.innerText.replace("1",(parseInt(newKeyIdx)+1));}
					}
				})
				if (currentNode.childNodes.length >0){
					currentNode.childNodes.forEach(replaceIdx);
				}
			}            
		</script>    
		<?php
		$meta_extensible_view = ob_get_clean();
		$extensible_view      = $extensible_view . $meta_extensible_view;
		return $extensible_view;
	}
}
