<?php declare(strict_types = 1);

// odsl-C:\wamp64\www\Nyalife-HMS-System\includes\controllers\api
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\ApiController.php' => 
    array (
      0 => 'b1d924ab2851384a0148f88b31b007904457505c',
      1 => 
      array (
        0 => 'apicontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'checkauthentication',
        2 => 'sendresponse',
        3 => 'senderror',
        4 => 'senderrorresponse',
        5 => 'requireauth',
        6 => 'validaterequest',
        7 => 'getintparam',
        8 => 'getstringparam',
        9 => 'getfloatparam',
        10 => 'getrequestdata',
        11 => 'validateparams',
        12 => 'getpatientid',
        13 => 'getstaffid',
        14 => 'bindparams',
        15 => 'isdebugmode',
        16 => 'handleexception',
        17 => 'processrequestdata',
        18 => 'execute',
        19 => 'fetchone',
        20 => 'fetchall',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\ApiNotificationsController.php' => 
    array (
      0 => '1ca0ba8006e1fc518b7f246c235cf881fde30d11',
      1 => 
      array (
        0 => 'apinotificationscontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'index',
        2 => 'count',
        3 => 'markasread',
        4 => 'markallasread',
        5 => 'requireauth',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\AppointmentController.php' => 
    array (
      0 => 'c560c42c48d6e707511ca7a32f2b5ceec94e6be8',
      1 => 
      array (
        0 => 'appointmentcontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'getqueryparams',
        2 => 'index',
        3 => 'get',
        4 => 'create',
        5 => 'update',
        6 => 'cancel',
        7 => 'getavailableslots',
        8 => 'pendingcount',
        9 => 'stats',
        10 => 'getappointmentstats',
        11 => 'hasaccesstoappointment',
        12 => 'hasschedulingconflict',
        13 => 'validate',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\CommunicationController.php' => 
    array (
      0 => '7f67412ee806cc6414e8c104d000072e12cdb2f2',
      1 => 
      array (
        0 => 'communicationcontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'getinbox',
        2 => 'getsent',
        3 => 'getarchived',
        4 => 'getmessage',
        5 => 'search',
        6 => 'sendmessage',
        7 => 'archivemessage',
        8 => 'deletemessage',
        9 => 'getusers',
        10 => 'markmessageasread',
        11 => 'getmessageusers',
        12 => 'logactivity',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\ConsultationController.php' => 
    array (
      0 => 'fa4a7b79da27c85129b40c51a299e34393bdd85e',
      1 => 
      array (
        0 => 'consultationcontroller',
      ),
      2 => 
      array (
        0 => 'saveconsultation',
        1 => 'finalizeconsultation',
        2 => 'saveprescription',
        3 => 'savelabrequest',
        4 => 'getlabrequests',
        5 => 'getprescriptions',
        6 => 'deletelabrequest',
        7 => 'deletereferral',
        8 => 'deletefollowup',
        9 => 'savefollowup',
        10 => 'deleteprescription',
        11 => 'savereferral',
        12 => 'checkconsultationaccess',
        13 => 'pendingcount',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\DoctorsController.php' => 
    array (
      0 => 'f53740b84706e1032a54711ed0b0744aaa66d332',
      1 => 
      array (
        0 => 'doctorscontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'index',
        2 => 'show',
        3 => 'byspecialization',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\FollowUpController.php' => 
    array (
      0 => '05e64a2c246aa0267002de6035e65f17cdccd31e',
      1 => 
      array (
        0 => 'followupcontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'index',
        2 => 'show',
        3 => 'store',
        4 => 'update',
        5 => 'delete',
        6 => 'getbypatient',
        7 => 'getbydoctor',
        8 => 'getbyconsultation',
        9 => 'upcoming',
        10 => 'statistics',
        11 => 'updatestatus',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\InsuranceController.php' => 
    array (
      0 => '6737d09fc62e3f893dfa505021e16ee45aad7f84',
      1 => 
      array (
        0 => 'insurancecontroller',
      ),
      2 => 
      array (
        0 => 'getpolicy',
        1 => 'savepolicy',
        2 => 'deletepolicy',
        3 => 'calculatecoverage',
        4 => 'getpatientpolicies',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\LabTestController.php' => 
    array (
      0 => 'cdfdb5a2d74b4585a74557686f868f14ddfdf780',
      1 => 
      array (
        0 => 'labtestcontroller',
      ),
      2 => 
      array (
        0 => 'gettestresult',
        1 => 'createtestrequest',
        2 => 'updateteststatus',
        3 => 'savetestresults',
        4 => 'getlabrequests',
        5 => 'checktestaccesspermission',
        6 => 'generatetestresulthtml',
        7 => 'isvaliddate',
        8 => 'getlabresults',
        9 => 'userhaspermissionforlabrequest',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\MedicationsController.php' => 
    array (
      0 => 'd04748c78f6bec48574c5ae0ee81db568d072b8e',
      1 => 
      array (
        0 => 'medicationscontroller',
      ),
      2 => 
      array (
        0 => 'getmedications',
        1 => 'getmedication',
        2 => 'savemedication',
        3 => 'togglestatus',
        4 => 'updatestock',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\PaymentController.php' => 
    array (
      0 => '5b6bce8984593f69ffb1dea84dbbdb4651aa591b',
      1 => 
      array (
        0 => 'paymentcontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'index',
        2 => 'show',
        3 => 'store',
        4 => 'update',
        5 => 'delete',
        6 => 'getbyinvoice',
        7 => 'getbypatient',
        8 => 'statistics',
        9 => 'getbymethod',
        10 => 'updatestatus',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\controllers\\api\\ValidationController.php' => 
    array (
      0 => '87b51a873084aa71128056a8627b9b1ec1ed4bd5',
      1 => 
      array (
        0 => 'validationcontroller',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'validateusername',
        2 => 'validateemail',
        3 => 'validateappointment',
        4 => 'getavailableslots',
        5 => 'getavailabledoctors',
      ),
      3 => 
      array (
      ),
    ),
  ),
));