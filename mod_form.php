<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->libdir . '/pagelib.php');
global $PAGE;
$PAGE->requires->js(new moodle_url('https://maps.googleapis.com/maps/api/js?key=AIzaSyDbGJgtBEZqgKfji5iH0HGyd3RzO4-qVOc&sensor=true') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/booking/googlemaps.js') );

class mod_booking_mod_form extends moodleform_mod {

	function definition() {
		global $CFG, $DB;

		$mform    = $this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('bookingname', 'booking'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('name', PARAM_TEXT);
        } else {
                $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('bookingtext', 'booking'));


        //Google maps integration---------------------------------------------------------
        $mform->addElement('header', '', 'Location');
        //TODO add moodle entry forms for lat-long so it can be taken for DB

        $mform->addElement('text', 'lat', '');
        $mform->setType('lat', PARAM_RAW);

        $mform->addElement('text', 'lng', '');
        $mform->setType('lng', PARAM_RAW);

        $mform->addElement('html', 
            '<div id="panel" style="float:left; margin-right:40px; width: 220px">
             <input onkeyup="addressKeyUp(event)" style="width:98%" id="address" type="textbox" placeholder="Address, postcode, or location"><br>
             <input type="button" value="Lookup address" style="width: 100%" onclick="codeAddress()">');      

        $mform->addElement('textarea', 'address', ''); 
        
                
        //Map instructions
        $mform->addElement('html', '<div id="mapinstructions">
            Search for location in text box. If not corect, drag marker to correct location and edit text. 
            </div>');
        
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div id="map-canvas" class="region-content" style="width: 500px; height: 500px; float: left"/>'); 
        
        
        $menuoptions=array();
        $menuoptions[0] = get_string('disable');
        $menuoptions[1] = get_string('enable');

        

        //Date time and duration of the event ----------------------------------------------------------
        $mform->addElement('header', 'datetime', 'Date and time');
        $mform->addElement('date_time_selector', 'dateandtime', 'Date and time of event');
        $mform->setType('coursestarttime', PARAM_INT);
        $mform->disabledIf('coursestarttime', 'startendtimeknown', 'notchecked');
        
        $mform->addElement('text', 'duration', 'Duration');


        //default options for booking options---------------------------------------------------------
        $mform->addElement('header', '', get_string('defaultbookingoption','booking'));

        $mform->addElement('select', 'limitanswers', get_string('limitanswers', 'booking'), $menuoptions);

        $mform->addElement('text', 'maxanswers', get_string('maxparticipantsnumber','booking'),0);
        $mform->disabledIf('maxanswers', 'limitanswers', 0);
        $mform->setType('maxanswers', PARAM_INT);

        $mform->addElement('text', 'maxoverbooking', get_string('maxoverbooking','booking'),0);
        $mform->disabledIf('maxoverbooking', 'limitanswers', 0);
        $mform->setType('maxoverbooking', PARAM_INT);



				

        // confirmation mesages----------------------------------------------------------------------------
        $mform->addElement('header', 'confirmation', get_string('confirmationmessagesettings', 'booking'));
        $mform->addElement('html', '<input type="checkbox" name="confmail_chk" id="confmail_chkbox" onchange="emailCheckBoxes(this)">Edit default confirmation messages?<br>');
        $mform->addElement('html', '<div id="hidemail">');

        $mform->addElement('selectyesno', 'sendmail', get_string("sendconfirmmail", "booking"));

        $mform->addElement('selectyesno', 'copymail', get_string("sendconfirmmailtobookingmanger", "booking"));

        $mform->addElement('text', 'bookingmanager', get_string('usernameofbookingmanager', 'booking'));
        $mform->setType('bookingmanager', PARAM_TEXT);
                $mform->setDefault('bookingmanager', 'admin');
                $mform->disabledIf('bookingmanager', 'copymail', 0);

        // Add the fields to allow editing of the default text:
        $context = get_context_instance(CONTEXT_SYSTEM);
        $editoroptions = array('subdirs' => false, 'maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => false, 'context' => $context);
        $fieldmapping = (object)array(
            'status' => '{status}',
            'participant' => '{participant}',
            'title' => '{title}',
            'duration' => '{duration}',
            'starttime' => '{starttime}',
            'endtime' => '{endtime}',
            'startdate' => '{startdate}',
            'enddate' => '{enddate}',
            'courselink' => '{courselink}',
            'bookinglink' => '{bookinglink}'
        );

        $mform->addElement('editor', 'bookedtext', get_string('bookedtext', 'booking'), null, $editoroptions);
        $default = array(
            'text' => get_string('confirmationmessage', 'mod_booking', $fieldmapping),
            'format' => FORMAT_HTML
        );
        $default['text'] = str_replace("\n", '<br/>', $default['text']);
        $mform->setDefault('bookedtext', $default);
        $mform->addHelpButton('bookedtext', 'bookedtext', 'mod_booking');

        $mform->addElement('editor', 'waitingtext', get_string('waitingtext', 'booking'), null, $editoroptions);
        $default = array(
            'text' => get_string('confirmationmessagewaitinglist', 'mod_booking', $fieldmapping),
            'format' => FORMAT_HTML
        );
        $default['text'] = str_replace("\n", '<br/>', $default['text']);
        $mform->setDefault('waitingtext', $default);
        $mform->addHelpButton('waitingtext', 'waitingtext', 'mod_booking');

        $mform->addElement('editor', 'statuschangetext', get_string('statuschangetext', 'booking'), null, $editoroptions);
       
        $default = array(
            'text' => get_string('statuschangebookedmessage', 'mod_booking', $fieldmapping),
            'format' => FORMAT_HTML
        );
        $default['text'] = str_replace("\n", '<br/>', $default['text']);
        $mform->setDefault('statuschangetext', $default);
        $mform->addHelpButton('statuschangetext', 'statuschangetext', 'mod_booking');

        $mform->addElement('editor', 'deletedtext', get_string('deletedtext', 'booking'), null, $editoroptions);
        $default = array(
            'text' => get_string('deletedbookingusermessage', 'mod_booking', $fieldmapping),
            'format' => FORMAT_HTML
        );
        $default['text'] = str_replace("\n", '<br/>', $default['text']);
        $mform->setDefault('deletedtext', $default);
        $mform->addHelpButton('deletedtext', 'deletedtext', 'mod_booking');
        
        $mform->addElement('html', '</div>');//end hid div
        
        //Miscellaneous settings
	$mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));
        $mform->addElement('html', '<input type="checkbox" name="bookpol_chk" id="bookpol_chkbox" onchange="bookPolCheckbox(this)">Add a default booking policy?');
        $mform->addElement('html','<div id="hidebookpol">');

	$mform->addElement('editor', 'bookingpolicy', get_string("bookingpolicy", "booking"), null, null);
        $mform->setType('bookingpolicy', PARAM_CLEANHTML);
        $mform->addElement('html','</div>');//end hide

        $mform->addElement('selectyesno', 'allowupdate', get_string("allowdelete", "booking"));

        $mform->addElement('selectyesno', 'autoenrol', get_string('autoenrol', 'booking'));
        $mform->addHelpButton('autoenrol', 'autoenrol', 'booking');

        $opts = array(0 => get_string('unlimited', 'mod_booking'));
        $extraopts = array_combine(range(1, 100), range(1, 100));
        $opts = $opts + $extraopts;
        $mform->addElement('select', 'maxperuser', get_string('maxperuser', 'mod_booking'), $opts);
        $mform->setDefault('maxperuser', 0);
        $mform->addHelpButton('maxperuser', 'maxperuser', 'mod_booking');
	

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
       
	}

	function data_preprocessing(&$default_values){
		if (empty($default_values['timeopen'])) {
			$default_values['timerestrict'] = 0;
		} else {
			$default_values['timerestrict'] = 1;
		}
        if (!isset($default_values['bookingpolicyformat'])) {
            $default_values['bookingpolicyformat'] = FORMAT_HTML;
        }
        if (!isset($default_values['bookingpolicy'])) {
            $default_values['bookingpolicy'] = '';
        }
        $default_values['bookingpolicy'] = array('text'=>$default_values['bookingpolicy'],'format'=>$default_values['bookingpolicyformat']);

        if (isset($default_values['bookedtext'])) {
            $default_values['bookedtext'] = array('text' => $default_values['bookedtext'], 'format' => FORMAT_HTML);
        }
        if (isset($default_values['waitingtext'])) {
            $default_values['waitingtext'] = array('text' => $default_values['waitingtext'], 'format' => FORMAT_HTML);
        }
        if (isset($default_values['statuschangetext'])) {
            $default_values['statuschangetext'] = array('text' => $default_values['statuschangetext'], 'format' => FORMAT_HTML);
        }
        if (isset($default_values['deletedtext'])) {
            $default_values['deletedtext'] = array('text' => $default_values['deletedtext'], 'format' => FORMAT_HTML);
        }
	}
       
    function get_data() {
        $data = parent::get_data();
        if ($data) {
            $data->bookingpolicyformat = $data->bookingpolicy['format'];
            $data->bookingpolicy = $data->bookingpolicy['text'];
        }
      
        return $data;
    }
    
}
?>
