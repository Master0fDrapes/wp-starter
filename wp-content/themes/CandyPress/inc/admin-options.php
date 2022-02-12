<?php 
// Control core classes for avoid errors
if( class_exists( 'CSF' ) ) {

  //
  // Set a unique slug-like ID
  $prefix = 'tp_opt';

  //
  // Create options
  CSF::createOptions( $prefix, array(
    'menu_title' => 'Theme Options',
    'menu_slug'  => 'my-framework',
    'framework_title' => 'Theme Options',
  ) );

  // logo
  CSF::createSection( $prefix, array(
    'title'  => 'Header',
    'fields' => array(

      array(
        'id'    => 'logo',
        'type'  => 'media',
        'title' => 'Logo Main',
        'preview' => false,
        'library' => 'image',
      ),
    )
  ) );
  // Footer
  CSF::createSection( $prefix, array(
    'title'  => 'Footer',
    'fields' => array(
      array(
        'id'      => 'footer-about',
        'type'    => 'textarea',
        'title'   => 'Footer Content',
        'default' => 'Lorem ipsum dollar.'
      ),
    )
  ) );

  // Social Media Tab
  CSF::createSection( $prefix, array(
    'title'  => 'Social Media',
    'fields' => array(

    )
  ));  

  // Google Analytics
  CSF::createSection( $prefix, array(
    'title'  => 'Google Analytics',
    'fields' => array(
      array(
        'id'      => 'google-analytics',
        'type'    => 'textarea',
        'title'   => 'Google Analytics Code',
        'default' => ''
      ),
    )
  ));

}

?>