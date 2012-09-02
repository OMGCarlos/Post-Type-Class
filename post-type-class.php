<?php /*================================================================================
| post-type-class.php
|
| Created by: 	Carlos Ramos
| Twitter: 		@OMGCarlos https://twitter.com/#!/omgcarlos
| Portfolio: 	http://press12.com
| @version 		1.0.0
|
| @package  	WordPress
| @since  		1.0.0
|

Post Type Class
===============
This class allows you to build WordPress Custom Post Types (CPT's) in as little 
as 2 lines of code! The class features the following:

* Create CPT's
* Create and populate meta boxes
* Easy debugging
* Automatic Nonce security

*************************************************************

## Creating a New CPT ##

    $obj = new PostType($cptName, $cptArgs);

**`$cptName`**    _(STRING)_    The CPT slug.  
**`$cptArgs`**    _(Array)_     CPT arguments as defined by [`register_post_type()`](http://codex.wordpress.org/Function_Reference/register_post_type)

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

**Input:**
    
    $homeSlideshow = new PostType(
        'home-slideshow',
        array(
            'label'        => 'Home Slideshow',
            'public'       => true,
            'show_in_menu' => 25,
            'supports'     => array('title', 'editor', 'thumbnail')
        )
    )

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    
**Output:**
Creates a custom post type named `home-slideshow` and positions it's menu 
underneath Comments. It is now fully functional and allows you to edit its 
Title, content, and thumbnail!

*************************************************************

## Adding Meta Boxes ##

    $obj->meta_box($metaLabel, $metaForm);

**`$metaLbael`**    _(STRING)_ Meta box label. May include basic HTML styling tags.
**`$metaForm`**    _(ARRAY)_ List of label/input elements to create.

**`$metaForm`** should be in the form:

    'Label', 'Post Meta Key',
    'Label', 'Post Meta Key',
    'Label', 'Post Meta Key',
    ...

Where `Label` is the labels text, and `Post Meta Key` is the key used in 
[`get_post_meta()`](http://codex.wordpress.org/Function_Reference/get_post_meta)

When the `add_meta_box` call is defined, its arguments are generated as follows:

    $id         = sanitize_title( $obj->cptArgs->label );
    $title      = $metaLabel
    $callback   = $this->meta_box_cb (a unique closure)
    $post_type  = $obj->cptName
    $context    = DEFAULT
    $priority   = DEFAULT
    $callback_args = NONE, the closure automatically pulls arguments

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

**Input:**
    
    $homeSlideshow->metabox(
        'Home Slideshow Properties',
        array(
            'Slide "Read More" label', '_slideReadMore',
            'Slide links to', '_slideLinksTo'
        )
    )

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    
**Output:**
Creates a meta box labeled "Home Slide Properties", with two sets of labels 
_(Slide "Read More" label and "Slide links to)_ and text input boxes 
_(#slide-read-more-label and #slide-links-to)_. It also saves two hidden custom 
fields.


*************************************************************

## Options ##
Below are a list of additional options you can set and their default values:

    $obj->nonce->name    = $this->cptName . '_nonce';
    $obj->nonce->action  = $this->cptName . '_action';

    $obj->cap            = 'edit_page';


**`$obj->nonce->name`** and **`obj->nonce->action`** correspond to the `$name` 
and `$action` arguments in 
[`wp_nonce_field`](http://codex.wordpress.org/Function_Reference/wp_nonce_field) 
which is checked on save. Change these if you'd you need to.

**`$obj->cap`** is used to set the capability requirements for saving the page, 
which defaults to 'edit_page'. If your CPT requires admin acces simply do 
something as follows:

    $homeSlideshow->cap = 'manage_options';

*************************************************************

TO DO
=====
1. Trap illegal $cptName names
2. Add aliases for function names. Ex) `$obj->meta_box() === $obj->mb()`
3. Define basic HTML tags for `$obj->meta_box()`
4. Add granularity to `$obj->meta_box()`
|===============================================================================*/


	class PostType{
		/*================================================================================
		| Setup Properties
		================================================================================*/
		/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		| User Defined
		- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
		public $cptName = 'NULL';
		public $cptArgs = 'NULL';
		public $metaName = '';
		public $metaLabel = '';
		public $metaForm = array();
		public $nonce = array(
			'name'		=> '',
			'action'	=> ''
		);
		public $cap = 'edit_page';

		/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		| Control
		- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
		public $metaBoxes = array();


		/*================================================================================
		| Constructor
		================================================================================*/
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
		}

		/*================================================================================
		| Register Actions/hooks
		================================================================================*/
		function create(){
			add_action( 'init', array(&$this, 'register'));
			add_action( 'add_meta_boxes', array(&$this, 'add_meta_box'));
			add_action( 'save_post', array(&$this, 'save'));
		}

		/*================================================================================
		| Create the post type
		================================================================================*/
		function register(){
			register_post_type($this->cptName, $this->cptArgs);
		}

		/*================================================================================
		| Save the post type
		================================================================================*/
		function save($postID){
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
				$flip = false; 	//Check out $obj->add_meta_box() for more info on this var
				$label = '';	//Grab the label
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


		/*================================================================================
		| Begin the metabox-creation process
		================================================================================*/
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
		}


		/*================================================================================
		| Add the Meta Boxes
		================================================================================*/
		function add_meta_box(){
			foreach($this->metaBoxes as $box){
				$obj = $this;

				add_meta_box(
					'meta-box-' . sanitize_title( $box['label'] ),
					$box['label'],

					/*================================================================================
					| Now, Create the form
					================================================================================*/
					function() use($obj, $box){
						/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
						| Oh the $flip variable.
						|
						| Aight so this is how this shizznet works: 
						| $flip starts off as false and flips boolean at the end of every step
						| Therefore, if !$flip then $item = label
						| else $item = post meta
						|
						| Remember that the form elements are created in meta_box() in pairs. Hence $flip
						- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
						global $post;
						$flip = false;
						$label = '';	//Grab the label, ie, grab $box['form'] when $flip = false (every other time, on the odd)

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
										echo '<td><input id="' . sanitize_title( $label ) . '" name="' . sanitize_title( $label ) . '" value="' . get_post_meta($post->ID, $item, true) . '"></td>';
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