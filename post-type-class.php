<?php
/**
* @author 	Carlos Ramos https://twitter.com/#!/omgcarlos
* @link  	(@OMGCarlos,  	https://twitter.com/#!/omgcarlos)
* @link  	(Press12, 		https://press12.com)
* @version  1.0.2
*
* @package  WordPress
* @since  	1.0.0
*/
	/**
	 * The PostType Class
	 */
	class PostType{
		/*================================================================================
		| Setup Properties
		================================================================================*/
		/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		| User Defined
		- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
		/** @var string CPT Slug */
		public $cptName = 'NULL';
		/** @var array 	register_post_type arguments */
		public $cptArgs = 'NULL';
		/** @var string Meta box caption */
		public $metaLabel = '';
		/**
		 * Label/Post meta pair
		 *
		 * Should be in the form:
		 * 'label', 'meta',
		 * 'label', 'meta',
		 * ...
		 * 
		 * @var array
		 */
		public $metaForm = array();
		/** @var array wp_nonce_field arguments */
		public $nonce = array(
			'name'		=> '',
			'action'	=> ''
		);
		/** @var string Capability required for saving post */
		public $cap = 'edit_page';

		/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		| Control
		- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
		/** @var array List of metaboxes to display */
		public $metaBoxes = array();


		/**
		 * Initializes the object
		 * 
		 * @param string $cptName CPT slug
		 * @param array $cptArgs register_post_type arguments
		 *
		 * @return boolean true if success, false if not
		 */
		function __construct($cptName = 'NULL', $cptArgs = 'NULL'){
			/*================================================================================
			| Trap errors
			================================================================================*/
			$error = false;
			/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			| NULL arguments
			- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
			if( $cptName === 'NULL' ) {				
				trigger_error( __('PostType creation failed: $cptName not a STRING'), E_USER_WARNING );
				$error = true;
			}
			if( $cptArgs === 'NULL' ) {				
				trigger_error( __('PostType creation failed: $cptArgs not an ARRAY'), E_USER_WARNING );
				$error = true;
			}
			if($error) return false;
			/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			| Invalid arguments
			- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
			if( gettype($cptName) !== 'string' ) {				
				trigger_error(  __('PostType creation failed: $cptName must be a STRING'), E_USER_WARNING );
				$error = true;
			}
			if( gettype($cptArgs) !== 'array' ) {				
				trigger_error( __('PostType creation failed: $cptArgs must be an ARRAY'), E_USER_WARNING );				
				$error = true;
			}
			if($error) return false;

			/*================================================================================
			| Setup variables
			================================================================================*/
			$this->cptName = $cptName;
			$this->cptArgs = $cptArgs;
			$this->nonce['name'] = $cptName . '_name';
			$this->nonce['action'] = $cptName . '_action';

			return true;
		}

		/**
		 * Create the Post Type. This *MUST* be called in order for the post type to show
		 * and *MUST* be the last method called on this object, or duplicates may occur!
		 * 
		 * @return true
		 */
		function create(){
			add_action( 'init', array(&$this, 'register'));
			add_action( 'add_meta_boxes', array(&$this, 'add_meta_box'));
			add_action( 'save_post', array(&$this, 'save'));

			return true;
		}

		/*================================================================================
		| Create the post type
		================================================================================*/
		/**
		 * Register the post type
		 * 
		 * @return true
		 */
		function register(){
			register_post_type($this->cptName, $this->cptArgs);

			return true;
		}

		/*================================================================================
		| Save the post type
		================================================================================*/
		/**
		 * Saves the post type
		 * 
		 * @param  int $postID $post->ID
		 * @return NULL. Called from within a closure, so no point in returning anything.
		 */
		function save($postID){
			/*================================================================================
			| Security
			================================================================================*/
			if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			if( !current_user_can($this->cap, $postID)) return;
			if( !isset($_POST[$this->nonce['name']]) || !wp_verify_nonce( $_POST[$this->nonce['name']], $this->nonce['action'])) return;

			/*================================================================================
			| Loop through each Metabox
			================================================================================*/
			foreach($this->metaBoxes as $box){
				/*================================================================================
				| Loop through each input and add the post meta
				================================================================================*/
				/**
				 * @see $obj->add_meta_box()
				 * @var boolean
				 */
				$flip = false;
				/** @var string Grab the label, which is always one step before the post_meta */
				$label = '';
				foreach($box['form'] as $item){
					/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					| Grab the Label
					- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
					if( $flip = !$flip ) {
						$label = $item;
					/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
					| Create the label/input
					- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
					} else {
						update_post_meta( $postID, $item, $_POST[ sanitize_title( $label ) ] );
					}
				}
			}
		}


		/**
		 * Begin the metabox creation process
		 * 
		 * @param  string $metaLabel See properties above
		 * @param  array $metaForm  See properties above
		 * @return bool true if success, false if not. Note that it will throw an error as well on false
		 */
		function meta_box( $metaLabel = 'NULL', $metaForm = 'NULL' ){
			/*================================================================================
			| Trap errors
			================================================================================*/
			$error = false;
			/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			| NULL arguments
			- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
			if( $metaLabel === 'NULL' ) {				
				trigger_error( __('PostType Meta Box creation failed: $metaLabel not a STRING'), E_USER_ERROR );
				$error = true;
			}
			if( $metaForm === 'NULL' ) {				
				trigger_error( __('PostType Meta Box creation failed: $metaForm not an ARRAY'), E_USER_ERROR );
				$error = true;
			}
			if($error) return false;
			/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			| Invalid arguments
			- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
			if( gettype($metaLabel) !== 'string' ) {				
				trigger_error( __('PostType Meta Box creation failed: $metaLabel must be a STRING'), E_USER_ERROR );				
				$error = true;
			}
			if( gettype($metaForm) !== 'array' ) {				
				trigger_error(  __('PostType Meta Box creation failed: $metaForm must be an ARRAY'), E_USER_ERROR );
				$error = true;
			}
			if($error) return false;

			/*================================================================================
			| Add new metabox to the metabox array
			================================================================================*/
			array_push(
				$this->metaBoxes, 
				array(
					'label'	=> $metaLabel, 
					'form'	=> $metaForm 
				)
			);

			return true;
		}


		/**
		 * Actually add the meta box to the page
		 */
		function add_meta_box(){
			foreach($this->metaBoxes as $box){
				$obj = $this;

				add_meta_box(
					'meta-box-' . sanitize_title( $box['label'] ),
					$box['label'],

					/**
					 * Closure. These need to be generated on the fly.
					 */
					function() use($obj, $box){
						global $post;
						/**
						 * Oh the $flip variable.
						 *
						 * Aight so this is how this shizznet works: 
						 * 
						 * $flip starts off as false and flips boolean at the end of every step
						 * Therefore, if !$flip then $item = label
						 * else $item = post meta
						 * 
						 * Remember that the form elements are created in meta_box() in pairs. Hence $flip
						 * 
						 * @var boolean
						 */
						$flip = false;
						/** @var string Grab the label, ie, grab $box['form'] when $flip = false (every other time, on the odd) */
						$label = '';

						/*================================================================================
						| Echo the metabox to the page
						================================================================================*/
						echo '<table>';
							wp_nonce_field($obj->nonce['action'], $obj->nonce['name']);

							foreach($box['form'] as $item) {
								/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								| Grab the Label
								- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
								if( $flip = !$flip ) {
									$label = $item;
								/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
								| Create the label/input
								- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
								} else {
									echo '<tr>';
										echo '<td><label for="' . sanitize_title( $label ) . '">' . $label . '</label></td>';
										echo '<td><input type="text" id="' . sanitize_title( $label ) . '" name="' . sanitize_title( $label ) . '" value="' . get_post_meta($post->ID, $item, true) . '"></td>';
									echo '</tr>';
								}
							}
						echo '</table>';
					},
					$this->cptName	//The CPT
				);
			}
		}
	}

?>