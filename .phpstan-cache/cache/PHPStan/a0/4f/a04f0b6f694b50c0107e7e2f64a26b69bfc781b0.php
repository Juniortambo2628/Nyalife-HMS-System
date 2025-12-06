<?php declare(strict_types = 1);

// odsl-C:\wamp64\www\Nyalife-HMS-System\includes\models
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\AppointmentModel.php' => 
    array (
      0 => '3a00da294b9a975260e4e178c8619a49d21b7e47',
      1 => 
      array (
        0 => 'appointmentmodel',
      ),
      2 => 
      array (
        0 => 'getdoctorappointmentcount',
        1 => 'getdoctorappointmentsbystatus',
        2 => 'getdoctorappointments',
        3 => 'getpatientappointments',
        4 => 'getappointmentsfiltered',
        5 => 'getstatusclass',
        6 => 'getappointmentdetails',
        7 => 'getavailabletimeslots',
        8 => 'createappointment',
        9 => 'updatestatus',
        10 => 'updateappointment',
        11 => 'cancelappointment',
        12 => 'addmedicalhistory',
        13 => 'getupcomingappointments',
        14 => 'gettodayappointments',
        15 => 'countappointmentsbystatus',
        16 => 'countdoctorappointments',
        17 => 'countdoctorpatients',
        18 => 'getallappointmentsbydate',
        19 => 'getupcomingappointmentsbydaterange',
        20 => 'getupcomingdoctorappointments',
        21 => 'getpastpatientappointments',
        22 => 'gettotalappointmentscount',
        23 => 'getcount',
        24 => 'getcountbystatus',
        25 => 'getappointmentswithoutconsultation',
        26 => 'istimeslotavailable',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\BaseModel.php' => 
    array (
      0 => '198811db62a2c2859e0465e94189fe311c2c6e06',
      1 => 
      array (
        0 => 'basemodel',
      ),
      2 => 
      array (
        0 => 'getdbconnection',
        1 => 'gettablecolumns',
        2 => '__construct',
        3 => 'find',
        4 => 'findall',
        5 => 'create',
        6 => 'update',
        7 => 'delete',
        8 => 'count',
        9 => 'begintransaction',
        10 => 'committransaction',
        11 => 'rollbacktransaction',
        12 => 'validate',
        13 => 'geterrors',
        14 => 'getrelated',
        15 => 'query',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\ConsultationModel.php' => 
    array (
      0 => '2d2c5a0262827aa986d25c20fe87f3a93cc27107',
      1 => 
      array (
        0 => 'consultationmodel',
      ),
      2 => 
      array (
        0 => 'getconsultationbyid',
        1 => 'getconsultationsbypatient',
        2 => 'getconsultations',
        3 => 'getconsultationbyappointment',
        4 => 'getstatusclass',
        5 => 'truncatetext',
        6 => 'deleteconsultation',
        7 => 'createconsultation',
        8 => 'updateconsultation',
        9 => 'getcount',
        10 => 'getcountbystatus',
        11 => 'updatefield',
        12 => 'updatevitalsigns',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\DepartmentModel.php' => 
    array (
      0 => '12f15049f2812262323f980bb83549efc839da47',
      1 => 
      array (
        0 => 'departmentmodel',
      ),
      2 => 
      array (
        0 => 'getalldepartmentswithstaffcount',
        1 => 'getbyname',
        2 => 'getactivedepartments',
        3 => 'getstaffindepartment',
        4 => 'createdepartment',
        5 => 'updatedepartment',
        6 => 'hasstaff',
        7 => 'getdepartmentstatistics',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\DoctorModel.php' => 
    array (
      0 => 'f3aa898afc2ca080761a75ddb42889fd41b5c88e',
      1 => 
      array (
        0 => 'doctormodel',
      ),
      2 => 
      array (
        0 => 'getalldoctorswithdetails',
        1 => 'getbyuserid',
        2 => 'getdoctorsbydepartment',
        3 => 'getdoctorsbyspecialization',
        4 => 'createdoctor',
        5 => 'updatedoctor',
        6 => 'getdoctorstatistics',
        7 => 'searchdoctors',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\DoctorScheduleModel.php' => 
    array (
      0 => 'cfa3f6958863b0126a9959245e5bd71310ac2f5a',
      1 => 
      array (
        0 => 'doctorschedulemodel',
      ),
      2 => 
      array (
        0 => 'getdoctorschedule',
        1 => 'getdoctorschedules',
        2 => 'savedoctorschedule',
        3 => 'deletedoctorschedule',
        4 => 'getavailabletimeslots',
        5 => 'getalldoctorswithschedules',
        6 => 'getdaynames',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\FollowUpModel.php' => 
    array (
      0 => '8e06c41d2b01ce3eea27886854932ce3172b1a95',
      1 => 
      array (
        0 => 'followupmodel',
      ),
      2 => 
      array (
        0 => 'getfollowupwithdetails',
        1 => 'getfollowupsbypatient',
        2 => 'getfollowupsbydoctor',
        3 => 'getfollowupsbyconsultation',
        4 => 'getupcomingfollowups',
        5 => 'createfollowup',
        6 => 'updatefollowup',
        7 => 'updatestatus',
        8 => 'getfollowupsfiltered',
        9 => 'countfollowupsfiltered',
        10 => 'getfollowupstatistics',
        11 => 'getstatusclass',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\InvoiceModel.php' => 
    array (
      0 => '104591dbc5d130985a3262e9890579c46bfcd5b2',
      1 => 
      array (
        0 => 'invoicemodel',
      ),
      2 => 
      array (
        0 => 'getinvoicewithitems',
        1 => 'getinvoiceitems',
        2 => 'getinvoicesbypatient',
        3 => 'getinvoicesbydoctor',
        4 => 'createinvoice',
        5 => 'addinvoiceitem',
        6 => 'updatestatus',
        7 => 'generateinvoicenumber',
        8 => 'getinvoicestatistics',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\LabAttachmentModel.php' => 
    array (
      0 => 'ab5ebb9498c7489105b7aee647a6385bb57ea3e0',
      1 => 
      array (
        0 => 'labattachmentmodel',
      ),
      2 => 
      array (
        0 => 'createattachment',
        1 => 'getattachmentsbysampleid',
        2 => 'getattachmentbyid',
        3 => 'updatecomment',
        4 => 'deleteattachment',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\LabParameterModel.php' => 
    array (
      0 => 'fbe3619d94e437f675ea05e854a35c5e1f0ef92f',
      1 => 
      array (
        0 => 'labparametermodel',
      ),
      2 => 
      array (
        0 => 'getparameterwithdetails',
        1 => 'getparametersbytesttype',
        2 => 'getactiveparameters',
        3 => 'getparametersfiltered',
        4 => 'countparametersfiltered',
        5 => 'createparameter',
        6 => 'updateparameter',
        7 => 'toggleactivestatus',
        8 => 'deleteparameter',
        9 => 'getparameterstatistics',
        10 => 'getparameterswithresults',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\LabRequestModel.php' => 
    array (
      0 => 'aeaf7cfac16df2ffad79f0ddd8a20c7708d438c9',
      1 => 
      array (
        0 => 'labrequestmodel',
      ),
      2 => 
      array (
        0 => 'getrequestwithdetails',
        1 => 'getrequestsbypatient',
        2 => 'getrequestsbyconsultation',
        3 => 'getpendingrequests',
        4 => 'getrequestsfiltered',
        5 => 'countrequestsfiltered',
        6 => 'createrequest',
        7 => 'updaterequest',
        8 => 'updatestatus',
        9 => 'assignrequest',
        10 => 'marksamplecollected',
        11 => 'getrequeststatistics',
        12 => 'getstatusclass',
        13 => 'getpriorityclass',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\LabTestModel.php' => 
    array (
      0 => '009b5035f331e06934feee8899c539bbc5a8c427',
      1 => 
      array (
        0 => 'labtestmodel',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'gettesttypebyid',
        2 => 'createtestrequest',
        3 => 'getrequestbyid',
        4 => 'updaterequest',
        5 => 'processtest',
        6 => 'getpendingtests',
        7 => 'getcompletedtests',
        8 => 'countcompletedtests',
        9 => 'getactivetesttypes',
        10 => 'registersample',
        11 => 'getsamplesbystatus',
        12 => 'countsamplesbystatus',
        13 => 'getsamplebyid',
        14 => 'updatesamplestatus',
        15 => 'savetestresult',
        16 => 'completesample',
        17 => 'getsampleresults',
        18 => 'gettestresults',
        19 => 'getpatienttests',
        20 => 'generaterequestnumber',
        21 => 'gettestsbyconsultation',
        22 => 'counttesttypes',
        23 => 'getalltesttypes',
        24 => 'getallparameters',
        25 => 'getparametersbytestid',
        26 => 'createtesttype',
        27 => 'updatetesttype',
        28 => 'createparameter',
        29 => 'updateparameter',
        30 => 'deleteparameter',
        31 => 'deleteparametersbytestid',
        32 => 'deletetesttype',
        33 => 'istesttypeinuse',
        34 => 'getrequestsbystatus',
        35 => 'countrequestsbystatus',
        36 => 'getrequestsbydoctor',
        37 => 'countrequestsbydoctor',
        38 => 'getrequestsbypatient',
        39 => 'countrequestsbypatient',
        40 => 'getrecenttestsbypatient',
        41 => 'updaterequeststatus',
        42 => 'ensuretestresultrecords',
        43 => 'savetestresults',
        44 => 'getcount',
        45 => 'getcountbystatus',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\MedicalHistoryModel.php' => 
    array (
      0 => 'fef62bf0fbf109cef802002e327bb363ac8fadaf',
      1 => 
      array (
        0 => 'medicalhistorymodel',
      ),
      2 => 
      array (
        0 => 'getpatientmedicalhistory',
        1 => 'addmedicalhistory',
        2 => 'updatemedicalhistory',
        3 => 'deletemedicalhistory',
        4 => 'getmedicalhistorybytype',
        5 => 'getongoingconditions',
        6 => 'getmedicalhistorysummary',
        7 => 'gethistorytypes',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\MedicationModel.php' => 
    array (
      0 => 'f37fd80483e98bf886bf3cdc847ffff14e7e50d3',
      1 => 
      array (
        0 => 'medicationmodel',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'getallmedications',
        2 => 'getmedicationbyid',
        3 => 'getcommonmedications',
        4 => 'createmedication',
        5 => 'updatemedication',
        6 => 'deletemedication',
        7 => 'ismedicationinuse',
        8 => 'getcategories',
        9 => 'getbatches',
        10 => 'addbatch',
        11 => 'updatebatch',
        12 => 'deletebatch',
        13 => 'updatestocklevel',
        14 => 'decrementstock',
        15 => 'getlowstockmedications',
        16 => 'getexpiringmedications',
        17 => 'gettotalmedications',
        18 => 'getmedicationcategories',
        19 => 'getmedicationforms',
        20 => 'getmedicationunits',
        21 => 'getlowstockcount',
        22 => 'getoutofstockcount',
        23 => 'updatemedicationstatus',
        24 => 'getmedicationstock',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\MessageModel.php' => 
    array (
      0 => '90903cd3e494fd88743f2f5b9147ff5df173589b',
      1 => 
      array (
        0 => 'messagemodel',
      ),
      2 => 
      array (
        0 => 'getinboxmessages',
        1 => 'getsentmessages',
        2 => 'getarchivedmessages',
        3 => 'getmessagewithdetails',
        4 => 'markasread',
        5 => 'archivemessage',
        6 => 'deletemessage',
        7 => 'getunreadcount',
        8 => 'sendmessage',
        9 => 'searchmessages',
        10 => 'getmessagestats',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\NotificationModel.php' => 
    array (
      0 => '244167217634ef45271e38f2d34a1b44fa179633',
      1 => 
      array (
        0 => 'notificationmodel',
      ),
      2 => 
      array (
        0 => 'create',
        1 => 'createappointmentnotification',
        2 => 'getbyuserid',
        3 => 'getbyguestemail',
        4 => 'markasread',
        5 => 'markallasread',
        6 => 'getunreadcount',
        7 => 'deleteoldnotifications',
        8 => 'updatestatus',
        9 => 'getprioritybytype',
        10 => 'getnotificationtemplate',
        11 => 'getusernotifications',
        12 => 'getusernotificationcount',
        13 => 'timeago',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\PatientModel.php' => 
    array (
      0 => 'acf34b49eac0384d9f258c2f5749ba6216601f3e',
      1 => 
      array (
        0 => 'patientmodel',
      ),
      2 => 
      array (
        0 => 'getbyid',
        1 => 'getbyuserid',
        2 => 'getbypatientnumber',
        3 => 'getwithuserdata',
        4 => 'getallpatientswithuserdata',
        5 => 'searchpatients',
        6 => 'getmedicalhistory',
        7 => 'addmedicalhistory',
        8 => 'createpatient',
        9 => 'updatepatient',
        10 => 'generatepatientnumber',
        11 => 'getallpatients',
        12 => 'getpatientidbyuserid',
        13 => 'getrecentpatientsbydoctor',
        14 => 'getpatientcountbydoctor',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\PaymentModel.php' => 
    array (
      0 => '05bcbcd5ebf2a9c8c2ddab4e36d5066dd36b85c6',
      1 => 
      array (
        0 => 'paymentmodel',
      ),
      2 => 
      array (
        0 => 'getpaymentwithdetails',
        1 => 'getpaymentsbypatient',
        2 => 'getpaymentsbyinvoice',
        3 => 'createpayment',
        4 => 'updatepaymentstatus',
        5 => 'updateinvoicepaymentstatus',
        6 => 'getpaymentstatistics',
        7 => 'getpaymentsbymethod',
        8 => 'getpaymentsfiltered',
        9 => 'countpaymentsfiltered',
        10 => 'updatepayment',
        11 => 'deletepayment',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\PrescriptionModel.php' => 
    array (
      0 => '2c57fa28454378104c405b83cded8a2b057d74b6',
      1 => 
      array (
        0 => 'prescriptionmodel',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'createprescription',
        2 => 'getprescriptionwithitems',
        3 => 'getallprescriptions',
        4 => 'getpendingprescriptions',
        5 => 'getcompletedprescriptions',
        6 => 'getpatientprescriptions',
        7 => 'getprescriptionitems',
        8 => 'getdoctorprescriptions',
        9 => 'updatestatus',
        10 => 'dispenseprescription',
        11 => 'cancelprescription',
        12 => 'generateprescriptionnumber',
        13 => 'getprescriptionsbydoctor',
        14 => 'countprescriptionsbydoctor',
        15 => 'getprescriptionsbystatus',
        16 => 'countprescriptions',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\StaffModel.php' => 
    array (
      0 => '922637300e1a70285b958a8b688fe78739385d08',
      1 => 
      array (
        0 => 'staffmodel',
      ),
      2 => 
      array (
        0 => 'getstaffidbyuserid',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\UserModel.php' => 
    array (
      0 => '237679197d85ef0c763325ad5269542760df54ce',
      1 => 
      array (
        0 => 'usermodel',
      ),
      2 => 
      array (
        0 => 'getbyusername',
        1 => 'getbyemail',
        2 => 'getwithrole',
        3 => 'authenticate',
        4 => 'updatelastlogin',
        5 => 'createremembertoken',
        6 => 'getactiveusercount',
        7 => 'checkremembertoken',
        8 => 'deleteremembertoken',
        9 => 'createuser',
        10 => 'updateprofile',
        11 => 'changepassword',
        12 => 'getallusers',
        13 => 'getrecentusers',
        14 => 'getuserbyid',
        15 => 'getallroles',
        16 => 'emailexists',
        17 => 'usernameexists',
        18 => 'updateuser',
        19 => 'deleteuser',
        20 => 'getdoctoridbyuserid',
        21 => 'getdoctors',
        22 => 'getalldoctors',
        23 => 'getusersbyrole',
        24 => 'getallactiveusers',
        25 => 'getcount',
        26 => 'getcountbyrole',
        27 => 'storepasswordresettoken',
        28 => 'getpasswordresettoken',
        29 => 'deletepasswordresettoken',
        30 => 'deletepasswordresettokenbyuserid',
        31 => 'cleanupexpiredresettokens',
      ),
      3 => 
      array (
      ),
    ),
    'C:\\wamp64\\www\\Nyalife-HMS-System\\includes\\models\\VitalSignModel.php' => 
    array (
      0 => 'aaad73b217ce8cd80630a19fb15f97a38a6d4901',
      1 => 
      array (
        0 => 'vitalsignmodel',
      ),
      2 => 
      array (
        0 => '__construct',
        1 => 'createvitalsign',
        2 => 'getvitalsignbyid',
        3 => 'getvitalsignsbypatient',
        4 => 'getlatestvitalsignbypatient',
        5 => 'updatevitalsign',
        6 => 'deletevitalsign',
      ),
      3 => 
      array (
      ),
    ),
  ),
));