<?php if (!defined('APPLICATION')) exit;

/**
 * Conversations API
 *
 * @package   API
 * @since     0.1.0
 * @author    Kasper Kronborg Isager <kasperisager@gmail.com>
 * @copyright Copyright 2013 © Kasper Kronborg Isager
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ConversationsAPI extends APIMapper
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
            'controller'   => 'Messages',
            'method'       => 'all',
            'authenticate' => true,
            'arguments'    => [
                'Page' => val('Page', $data)
            ]
        ]);

        static::get('/[i:ConversationID]', [
            'controller'   => 'Messages',
            'authenticate' => true,
            'arguments'    => [
                'Offset' => val('Offset', $data),
                'Limit'  => val('Limit', $data)
            ]
        ]);

        static::post('/', [
            'controller' => 'Messages',
            'method'     => 'add'
        ]);

        static::post('/[i:ConversationID]/messages', [
            'controller' => 'Messages',
            'method'     => 'addMessage'
        ]);

        static::delete('/[i:ConversationID]', [
            'controller' => 'Messages',
            'method'     => 'clear'
        ]);
    }
}
