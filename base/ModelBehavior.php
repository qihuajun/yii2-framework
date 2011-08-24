<?php
/**
 * ModelBehavior class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelBehavior class.
 *
 * ModelBehavior is a base class for behaviors that are attached to a model object.
 * The model should be an instance of [[Model]] or its child classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelBehavior extends Behavior
{
	/**
	 * Declares event handlers for owner's events.
	 * The default implementation returns the following event handlers:
	 *
	 * - `onAfterConstruct` event: [[afterConstruct]]
	 * - `onBeforeValidate` event: [[beforeValidate]]
	 * - `onAfterValidate` event: [[afterValidate]]
	 *
	 * You may override these event handler methods to respond to the corresponding owner events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		return array(
			'onAfterConstruct' => 'afterConstruct',
			'onBeforeValidate' => 'beforeValidate',
			'onAfterValidate' => 'afterValidate',
		);
	}

	/**
	 * Responds to [[Model::onAfterConstruct]] event.
	 * Override this method if you want to handle the corresponding event of the [[owner]].
	 * @param Event $event event parameter
	 */
	public function afterConstruct($event)
	{
	}

	/**
	 * Responds to [[Model::onBeforeValidate]] event.
	 * Override this method if you want to handle the corresponding event of the [[owner]].
	 * You may set the [[ValidationEvent::isValid|isValid]] property of the event parameter
	 * to be false to cancel the validation process.
	 * @param ValidationEvent $event event parameter
	 */
	public function beforeValidate($event)
	{
	}

	/**
	 * Responds to [[Model::onAfterValidate]] event.
	 * Override this method if you want to handle the corresponding event of the [[owner]].
	 * @param Event $event event parameter
	 */
	public function afterValidate($event)
	{
	}
}
