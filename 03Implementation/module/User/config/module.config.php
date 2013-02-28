<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'user' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/user/[:controller[/:action]][/:id]',
                    'constraints' => array(
                        'controller'=> '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'        => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'User\Controller',
                        'controller' => 'index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'user' => __DIR__ . '/../view',
        ),
    ),
);
