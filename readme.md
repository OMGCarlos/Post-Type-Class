Post Type Class
===============
This class allows you to build WordPress Custom Post Types (CPT's) in as little as 2 lines of code! The class features the following:

* Create CPT's
* Create and populate meta boxes
* Easy debugging
* Automatic Nonce security

*************************************************************

## Creating a New CPT ##

    $obj = new PostType($cptName, $cptArgs);
    $obj->create();

`$cptName`    _(STRING)_    The CPT slug.  
`$cptArgs`    _(Array)_     CPT arguments as defined by [`register_post_type()`](http://codex.wordpress.org/Function_Reference/register_post_type)

**NOTE:** Once you call the `create()` method, the $obj gets destroyed. This is to prevent any further manipulation of the object, which could create duplicate CPT's.

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
Creates a custom post type named `home-slideshow` and positions it's menu underneath Comments. It is now fully functional and allows you to edit its Title, content, and thumbnail!

*************************************************************

## Adding Meta Boxes ##

    $obj->meta_box(array($metaLabel, $metaForm));

`$metaLabel` _(STRING)_    Meta box label. May include basic HTML styling tags.  
`$metaForm`  _(ARRAY)_     Setup as follows

    array(
        'label'     => STRING,
        'meta'      => STRING,
        ['type'     => STRING = "text",]
        ['caption'  => STRING,]
        ['attribtues' => STRING]
    )

`label`   Label text. The `for` attribute and input `id` is `sanitize_title(` `label` )  
`meta`    The key used in [`get_post_meta()`](http://codex.wordpress.org/Function_Reference/get_post_meta).  
`type`    The input type. ( `DEFAULT = "text"` )  
`caption` The input caption, which is displayed underneath the input box.  
`attribtues` Allows you to set a list of additional attributes. It must be a single string, and **must only use quotes**! ex)

    'attributes' => 'min="0" max="100" style="background: red;"'

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

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
            array(
                'label'     => 'Read More: ',
                'meta'      => '_slideReadMore',
                'caption'   => 'text for the read more button'
            ),
            array(
                'label'     => 'Slide Links To: ',
                'meta'      => '_slideURL'
            ),
            array(
                'label'     => 'Extra text',
                'meta'      => '_slideExtra',
                'type'      => 'textarea'
            )
        )
    )

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    
**Output:**
Creates a meta box labeled "Home Slide Properties", with three sets of labels _("Read More" (with caption), "Slide Links To", and a textarea labeled "Extra Text")_ and 2 text input boxes plus 1 textarea _(#slide-read-more, #slide-links-to, #slide-extra-text)_.


*************************************************************

## Options ##
Below are a list of additional options you can set and their default values:

    $obj->nonce->name    = $this->cptName . '_nonce';
    $obj->nonce->action  = $this->cptName . '_action';

    $obj->cap            = 'edit_page';


`$obj->nonce->name` and `obj->nonce->action` correspond to the `$name` and `$action` arguments in [`wp_nonce_field`](http://codex.wordpress.org/Function_Reference/wp_nonce_field) which is checked on save. Change these if you'd you need to.

`$obj->cap` is used to set the capability requirements for saving the page, which defaults to 'edit_page'. If your CPT requires admin acces simply do something as follows:

    $homeSlideshow->cap = 'manage_options';

*************************************************************

TO DO
=====
2. Add aliases for function names. Ex) `$obj->meta_box() === $obj->mb()`
3. Define basic HTML tags for `$obj->meta_box()`
5. Add default values for input fields
6. Turn off debugging for optimization
7. Add support for textareas and other non INPUT elements
8. Allow for get_post_type array
9. Allow for styling
10. Set multiple default CPT settings

*************************************************************

CHANGE LOG
==========
**1.2.0** - Introduced attributes to $obj->meta_box() inputs  
**1.1.0** - Finer control over $obj->meta_box(), including addition of input captions
**1.0.0** - Initial release, can create post types with meta boxes which contain input[type="text"] elements