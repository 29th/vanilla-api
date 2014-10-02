<?php if (!defined('APPLICATION')) exit;

/**
 * Categories API
 *
 * @package   API
 * @since     0.1.0
 * @author    Kasper Kronborg Isager <kasperisager@gmail.com>
 * @copyright Copyright 2013 © Kasper Kronborg Isager
 * @license   http://opensource.org/licenses/MIT MIT
 */
class CategoriesAPI extends APIMapper
{
    /**
     * Register API endpoints
     *
     * @since  0.1.0
     * @access public
     * @param  array $data
     * @return void
     * @static
     */
    public static function register($data)
    {
        static::get('/', [
            'controller' => 'Categories',
            'method'     => 'all'
        ]);

        static::get('/[i:CategoryIdentifier]', [
            'controller' => 'Categories'
        ]);

        static::post('/', [
            'application' => 'Vanilla',
            'controller'  => 'Settings',
            'method'      => 'addCategory'
        ]);

        static::post('/[i:CategoryID]/discussions', [
            'controller' => 'Post',
            'method'     => 'discussion'
        ]);

        static::put('/[i:CategoryID]', [
            'application' => 'Vanilla',
            'controller'  => 'Settings',
            'method'      => 'editCategory'
        ]);

        static::delete('/[i:CategoryID]', [
            'application' => 'Vanilla',
            'controller'  => 'Settings',
            'method'      => 'deleteCategory'
        ]);
    }
}
