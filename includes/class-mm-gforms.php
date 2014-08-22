<?php if ( ! defined('ABSPATH') ) exit; // Exits when accessed directly

if ( ! class_exists('MM_GForms') )
{
	class MM_GForms
	{
		static public function send_notification($notification_id, $form_id, $lead_id)
		{
			// gets form
			$form = RGFormsModel::get_form_meta($form_id);

			if ( empty($form) )
			{
				return false;
			}

			// gets notification
			if ( empty($form['notifications'][$notification_id]) )
			{
				return false;
			}

			$notification = $form['notifications'][$notification_id];

			// gets lead
			$lead = RGFormsModel::get_lead($lead_id);

			if ( empty($lead) )
			{
				return false;
			}

			// sends notification
			return GFCommon::send_notification($notification, $form, $lead);
		}
	}
}

?>