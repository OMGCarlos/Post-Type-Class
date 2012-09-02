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

**`$cptName`**    _(STRING)_    The CPT slug.  
**`$cptArgs`**    _(Array)_     CPT arguments as defined by [`register_post_type()`](http://codex.wordpress.org/Function_Reference/register_post_type)

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

**`$metaLabel`** _(STRING)_    Meta box label. May include basic HTML styling tags.  
**`$metaForm`**  _(ARRAY)_     Setup as follows

    array(
        'label'     => STRING,
        'meta'      => STRING,
        ['type'     => STRING = "text",]
        ['caption'  => STRING]
    )

**`label`** Label text. The `for` attribute and input `id` is `sanitize_title(` **`label`** )  
**`meta`**  The key used in [`get_post_meta()`](http://codex.wordpress.org/Function_Reference/get_post_meta).  
**`type`**  The input type. ( `DEFAULT = "text"` )  
**`caption`**  The input caption, which is displayed underneath the input box.  

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
                
            )
            'Slide "Read More" label', '_slideReadMore',
            'Slide links to', '_slideLinksTo'
        )
    )

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
    
**Output:**
Creates a meta box labeled "Home Slide Properties", with two sets of labels _(Slide "Read More" label and "Slide links to)_ and text input boxes _(#slide-read-more-label and #slide-links-to)_. It also saves two hidden custom fields.


*************************************************************

## Options ##
Below are a list of additional options you can set and their default values:

    $obj->nonce->name    = $this->cptName . '_nonce';
    $obj->nonce->action  = $this->cptName . '_action';

    $obj->cap            = 'edit_page';


**`$obj->nonce->name`** and **`obj->nonce->action`** correspond to the `$name` and `$action` arguments in [`wp_nonce_field`](http://codex.wordpress.org/Function_Reference/wp_nonce_field) which is checked on save. Change these if you'd you need to.

**`$obj->cap`** is used to set the capability requirements for saving the page, which defaults to 'edit_page'. If your CPT requires admin acces simply do something as follows:

    $homeSlideshow->cap = 'manage_options';

*************************************************************

TO DO
=====
1. Trap illegal $cptName names
2. Add aliases for function names. Ex) `$obj->meta_box() === $obj->mb()`
3. Define basic HTML tags for `$obj->meta_box()`
4. Add granularity to `$obj->meta_box()`