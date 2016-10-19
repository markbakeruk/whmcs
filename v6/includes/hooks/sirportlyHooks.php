<?php

  /**
   * Sirportly WHMCS Support Tickets Module
   * @copyright Copyright (c) 2015 aTech Media Ltd
   * @version 3.0
   */

  use WHMCS\View\Menu\Item as MenuItem;

  include_once(ROOTDIR . "/includes/sirportly/config.php");
  include_once(ROOTDIR . "/includes/sirportly/functions.php");

  if (App::getCurrentFilename() == 'clientarea') {
    add_hook('ClientAreaPage', 1, function ($vars)
    {
      if (isset($_SESSION['uid'])) {
        ## Load the sirportly contact
        $sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);

        ## Fetch an array of sirportly contact_ids
        $contact_ids = sirportlyContacts($_SESSION['uid'], $_SESSION['cid']);

        ## Fetch the tickets
        $sirportlyTickets = sirportlyTickets($contact_ids);

        ## Count the tickets
        $sirportlyTicketCount = count($sirportlyTickets['results']);

        ## Set the ticket count
        $vars['clientsstats']['numtickets'] = $sirportlyTicketCount;
        $vars['clientsstats']['numactivetickets'] = $sirportlyTicketCount;

        ## Return the variables
        return $vars;
      }
    });

    add_hook('ClientAreaHomepagePanels', 1, function (MenuItem $homePagePanels)
    {
      ## Locate "Recent Support Tickets" panel
      $supportTickets = $homePagePanels->getChild('Recent Support Tickets');

      ## Ensure we've found the panel, return if we don't
      if (is_null($supportTickets)) {
        return;
      }

      ## Load the sirportly contact
      $sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);

      ## Fetch an array of sirportly contact_ids
      $contact_ids = sirportlyContacts($_SESSION['uid'], $_SESSION['cid']);

      ## Fetch the tickets
      $sirportlyTickets = sirportlyTickets($contact_ids);

      ## Count the tickets
      $sirportlyTicketCount = count($sirportlyTickets['results']);

      ## Fetch the 5 latest tickets
      $latestSirportlyTickets = array_slice($sirportlyTickets['results'], 0, 5, true);

      ## Remove any existing children
      foreach ($supportTickets->getChildren() as $child) {
        $supportTickets->removeChild( $child->getName() );
      }

      ## Check to see if we have any Sirportly tickets to display
      if ($sirportlyTicketCount > 0) {
        ## Set the order
        $order = 1;
        ## Loop through each of the tickets
        foreach ($latestSirportlyTickets as $key => $ticket) {
          $date = formatTimestamp($ticket[3], true);

          $child = $supportTickets->addChild("<strong>#{$ticket[1]} - {$ticket[2]}</strong></br><small>Last Updated: {$date}</small>", array(
            'uri' => "viewticket.php?tid={$ticket[1]}&c={$ticket[0]}",
            'order' => $order
          ));
          $order++;
        }
      } else {
        ## Display the "No Recent Tickets Found" message
        $child = $supportTickets->addChild("No Recent Tickets Found. If you need any help, please open a ticket.", array());
      }
      
      ## Create support PIN home page panel
      include(ROOTDIR . "/includes/sirportly/config.php");
      if ($sirportly_pin_panel) {
      	$sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);
      	$sirportlySupportPIN = FindSupportPIN($sirportlyContact);
      	$supportPhoneURL = 'tel://' . $sirportly_pin_panel_phone;

      	$homePagePanels->addChild('sirportly_pin', array(
      	  'label' => 'Support PIN',
      	  'icon' => 'fa-user',
      	  'order' => 160,
      	  'extras' => array(
      	      'color' => 'blue',
      	      'btn-link' => $supportPhoneURL,
      	      'btn-text' => 'Call support',
      	      'btn-icon' => 'fa-phone',
      	  ),
      	  'bodyHtml' => '<h4 align="center">' . $sirportlySupportPIN . '</h4>',
      	  'footerHtml' => '',
     	 ));
     	 
      }
    });
  }

  ## Add support PIN to secondary sidebar
  if ((APP::getCurrentFileName()=='supporttickets') or (APP::getCurrentFileName()=='submitticket')) {
	add_hook('ClientAreaSecondarySidebar', 1, function($secondarySidebar){
    	include(ROOTDIR . "/includes/sirportly/config.php");
		if($sirportly_pin_panel) {

			$sirportlyContact = findOrCreateSirportlyContact($_SESSION['uid'], $_SESSION['cid']);
			$sirportlySupportPIN = FindSupportPIN($sirportlyContact);
			$supportPhoneURL = 'tel://' . $sirportly_pin_panel_phone;
			
			$Support_PIN_Sidebar = $secondarySidebar->addChild('Support PIN', array(
				'label' => 'Support PIN',
				'uri' => '#',
				'icon' => 'fa-user'
			));
			
			$Support_PIN_Sidebar->addChild('Support PIN', array(
				'uri' => '',
				'label' => '<h4 align="center">' . $sirportlySupportPIN . '</h4>',
				'order' => 1
			));
			
			$Support_PIN_Sidebar->setFooterHtml(
				'<a href="' . $supportPhoneURL . '" class="btn btn-success btn-sm btn-block"><i class="fa fa-phone"></i> Call Support</a>'
			);			
		}
  	});
  }


  ## This doesn't deserve to live here
  if (App::getCurrentFilename() == 'submitticket') {
    add_hook('ClientAreaFooterOutput', 1, function ($vars)
    {

      ## Check to ensure the Sirportly support module is in use
      if (!Menu::Context('support_module') == 'sirportly') {
        return;
      }

      return '<script>
          function refreshCustomFields(input) {
            jQuery("#customFieldsContainer").load(
              "submitticket.php",
                { action: "fetchcustomfields", deptid: $(input).val() }
            );
          }
      </script>';
    });
  }

  if (App::getCurrentFilename() == 'viewticket') {
    add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar)
    {

      ## Required files
      include(ROOTDIR . "/includes/sirportly/config.php");

      ## Check to ensure the Sirportly support module is in use
      if (!Menu::Context('support_module') == 'sirportly') {
        return;
      }

      ## Fetch the ticket
      $sirportlyTicket = Menu::Context('sirportlyTicket');

      $supportPanel = $primarySidebar->addChild('Ticket Information', array(
        'label' => Lang::trans('ticketinfo'),
        'icon'  => 'fa-ticket',
        'class' => 'ticket-details-children'
      ));

      $child = $supportPanel->addChild('Subject', array(
        'label' => "<span class='title'>Subject</span><br>{$sirportlyTicket['subject']}",
        'order' => 1
      ));
      $child->setClass('ticket-details-children');

      $child = $supportPanel->addChild('Department', array(
        'label' => "<span class='title'>Department</span><br>{$sirportlyTicket['department']['name']}",
        'order' => 2
      ));
      $child->setClass('ticket-details-children');

      $submitted_at = formatTimestamp($sirportlyTicket['submitted_at'], true);
      $child = $supportPanel->addChild('Submitted', array(
        'label' => "<span class='title'>Submitted</span><br>{$submitted_at}",
        'order' => 3
      ));
      $child->setClass('ticket-details-children');

      $updated_at = formatTimestamp($sirportlyTicket['last_update_posted_at'], true);
      $child = $supportPanel->addChild('Last_Updated', array(
        'label' => "<span class='title'>Last Updated</span><br>{$updated_at}",
        'order' => 4
      ));
      $child->setClass('ticket-details-children');

      $child = $supportPanel->addChild('Priority', array(
        'label' => "<span class='title'>Priority</span><br>{$sirportlyTicket['priority']['name']}",
        'order' => 5
      ));
      $child->setClass('ticket-details-children');

      ## Footer
      $replyText = Lang::trans('supportticketsreply');

      $ticketClosed = ($sirportlyTicket['status']['status_type'] == '1');
      $showCloseButton = $closedStatusId;
      $class = $showCloseButton ? 'col-xs-6 col-button-left' : 'col-xs-12';

      $footer = '<div class="' . $class . '">
        <button class="btn btn-success btn-sm btn-block" onclick="jQuery(\'#ticketReply\').click()">
          <i class="fa fa-pencil"></i> ' . $replyText . '
        </button>
      </div>';

      if ($showCloseButton) {
        $footer .= '<div class="col-xs-6 col-button-right">
          <button class="btn btn-danger btn-sm btn-block"';

          if ($ticketClosed) {
            $footer .= 'disabled="disabled"><i class="fa fa-times"></i> ' . Lang::trans('supportticketsstatusclosed');
          } else {
            $footer .=  'onclick="window.location=\'?tid=' .  $sirportlyTicket['reference'] . '&amp;c=' . $sirportlyTicket['id'] . '&amp;closeticket=true\'"> <i class="fa fa-times"></i> ' . Lang::trans('supportticketsclose');
          }
        $footer .= '</button></div>';
      }

      $supportPanel->setFooterHtml($footer);
    });
  }

  if (App::getCurrentFilename() == 'viewticket' || App::getCurrentFilename() == 'submitticket' || App::getCurrentFilename() == 'supporttickets') {

    add_hook('ClientAreaSecondarySidebar', 1, function (MenuItem $secondarySidebar)
    {
      ## Check to ensure the Sirportly support module is in use
      if (!Menu::Context('support_module') == 'sirportly') {
        return;
      }

      $supportPanel = $secondarySidebar->addChild('Support', array(
        'label' => 'Support',
        'icon'  => 'fa-support',
      ));
      $child = $supportPanel->addChild('Tickets', array(
        'label' => 'My Support Tickets',
        'icon'  => 'fa-ticket',
        'uri'   => 'supporttickets.php',
        'order' => 1
      ));
      $child->setClass(App::getCurrentFilename() == 'supporttickets' ? 'active' : '');
      $child = $supportPanel->addChild('Announcements', array(
        'label' => 'Announcements',
        'icon'  => 'fa-list',
        'uri'   => 'announcements.php',
        'order' => 2
      ));
      $child = $supportPanel->addChild('Knowledgebase', array(
        'label' => 'Knowledgebase',
        'icon'  => 'fa-info-circle',
        'uri'   => 'knowledgebase.php',
        'order' => 3
      ));
      $child = $supportPanel->addChild('Downloads', array(
        'label' => 'Downloads',
        'icon'  => 'fa-download',
        'uri'   => 'downloads.php',
        'order' => 4
      ));
      $child = $supportPanel->addChild('Network_Status', array(
        'label' => 'Network Status',
        'icon'  => 'fa-rocket',
        'uri'   => 'serverstatus.php',
        'order' => 5
      ));
      $child = $supportPanel->addChild('Open_Ticket', array(
        'label' => 'Open Ticket',
        'icon'  => 'fa-comments',
        'uri'   => 'submitticket.php',
        'order' => 6
      ));
      $child->setClass(App::getCurrentFilename() == 'submitticket' ? 'active' : '');
    });
  }