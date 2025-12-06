var classes = [
    {
        "name": "ApiController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "checkAuthentication",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendResponse",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendErrorResponse",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "requireAuth",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateRequest",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getIntParam",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStringParam",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFloatParam",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRequestData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateParams",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientId",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStaffId",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "bindParams",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isDebugMode",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleException",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processRequestData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "execute",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "fetchOne",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "fetchAll",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 21,
        "nbMethods": 21,
        "nbMethodsPrivate": 20,
        "nbMethodsPublic": 1,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 84,
        "ccn": 64,
        "ccnMethodMax": 7,
        "externals": [
            "BaseController",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseController"
        ],
        "implements": [],
        "lcom": 5,
        "length": 493,
        "vocabulary": 104,
        "volume": 3303.32,
        "difficulty": 24.29,
        "effort": 80223.41,
        "level": 0.04,
        "bugs": 1.1,
        "time": 4457,
        "intelligentContent": 136.02,
        "number_operators": 153,
        "number_operands": 340,
        "number_operators_unique": 13,
        "number_operands_unique": 91,
        "cloc": 157,
        "loc": 432,
        "lloc": 275,
        "mi": 53.74,
        "mIwoC": 13.54,
        "commentWeight": 40.2,
        "kanDefect": 3.29,
        "relativeStructuralComplexity": 576,
        "relativeDataComplexity": 1.67,
        "relativeSystemComplexity": 577.67,
        "totalStructuralComplexity": 12096,
        "totalDataComplexity": 35.16,
        "totalSystemComplexity": 12131.16,
        "package": "\\",
        "pageRank": 0.02,
        "afferentCoupling": 3,
        "efferentCoupling": 4,
        "instability": 0.57,
        "violations": {}
    },
    {
        "name": "ApiNotificationsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "count",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAllAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "requireAuth",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 6,
        "nbMethods": 6,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 5,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 15,
        "ccn": 10,
        "ccnMethodMax": 4,
        "externals": [
            "WebController",
            "NotificationService",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 147,
        "vocabulary": 41,
        "volume": 787.56,
        "difficulty": 11.74,
        "effort": 9242.25,
        "level": 0.09,
        "bugs": 0.26,
        "time": 513,
        "intelligentContent": 67.11,
        "number_operators": 33,
        "number_operands": 114,
        "number_operators_unique": 7,
        "number_operands_unique": 34,
        "cloc": 23,
        "loc": 96,
        "lloc": 73,
        "mi": 72.11,
        "mIwoC": 37.73,
        "commentWeight": 34.38,
        "kanDefect": 0.36,
        "relativeStructuralComplexity": 144,
        "relativeDataComplexity": 0.01,
        "relativeSystemComplexity": 144.01,
        "totalStructuralComplexity": 864,
        "totalDataComplexity": 0.08,
        "totalSystemComplexity": 864.08,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "AppointmentController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cancel",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "calendar",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "roleBasedRedirect",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "get",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAsCompleted",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAvailableSlots",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "stats",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "start",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "checkIn",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 17,
        "nbMethods": 17,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 17,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 196,
        "ccn": 180,
        "ccnMethodMax": 26,
        "externals": [
            "WebController",
            "AppointmentModel",
            "UserModel",
            "PatientModel",
            "StaffModel",
            "AuditLogger",
            "NotificationService",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 1971,
        "vocabulary": 296,
        "volume": 16180.83,
        "difficulty": 34.82,
        "effort": 563413.16,
        "level": 0.03,
        "bugs": 5.39,
        "time": 31301,
        "intelligentContent": 464.7,
        "number_operators": 455,
        "number_operands": 1516,
        "number_operators_unique": 13,
        "number_operands_unique": 283,
        "cloc": 249,
        "loc": 923,
        "lloc": 674,
        "mi": 36.03,
        "mIwoC": 0,
        "commentWeight": 36.03,
        "kanDefect": 6.99,
        "relativeStructuralComplexity": 1089,
        "relativeDataComplexity": 1.16,
        "relativeSystemComplexity": 1090.16,
        "totalStructuralComplexity": 18513,
        "totalDataComplexity": 19.76,
        "totalSystemComplexity": 18532.76,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 10,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "CommunicationController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInbox",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getSent",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getArchived",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "search",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "archiveMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUsers",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markMessageAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMessageUsers",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logActivity",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 13,
        "nbMethods": 13,
        "nbMethodsPrivate": 2,
        "nbMethodsPublic": 11,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 58,
        "ccn": 46,
        "ccnMethodMax": 7,
        "externals": [
            "ApiController",
            "MessageModel",
            "NotificationService"
        ],
        "parents": [
            "ApiController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 587,
        "vocabulary": 101,
        "volume": 3908.37,
        "difficulty": 32.5,
        "effort": 127022.03,
        "level": 0.03,
        "bugs": 1.3,
        "time": 7057,
        "intelligentContent": 120.26,
        "number_operators": 147,
        "number_operands": 440,
        "number_operators_unique": 13,
        "number_operands_unique": 88,
        "cloc": 97,
        "loc": 339,
        "lloc": 242,
        "mi": 53.51,
        "mIwoC": 16.66,
        "commentWeight": 36.85,
        "kanDefect": 1.57,
        "relativeStructuralComplexity": 676,
        "relativeDataComplexity": 0.64,
        "relativeSystemComplexity": 676.64,
        "totalStructuralComplexity": 8788,
        "totalDataComplexity": 8.33,
        "totalSystemComplexity": 8796.33,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "ConsultationController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "print",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateField",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateVitals",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "jsonResponse",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "formatUpdatedFields",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "formatVitalsForJson",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 13,
        "nbMethods": 13,
        "nbMethodsPrivate": 3,
        "nbMethodsPublic": 10,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 244,
        "ccn": 232,
        "ccnMethodMax": 62,
        "externals": [
            "WebController",
            "ConsultationModel",
            "AppointmentModel",
            "PatientModel",
            "LabTestModel",
            "UserModel",
            "AuditLogger",
            "Exception",
            "Exception",
            "UserModel",
            "Exception",
            "Exception",
            "UserModel",
            "SessionManager",
            "SessionManager",
            "Exception",
            "Exception",
            "UserModel",
            "Exception",
            "VitalSignModel",
            "ErrorHandler",
            "UserModel",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "VitalSignModel",
            "ErrorHandler",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "Exception"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 1860,
        "vocabulary": 262,
        "volume": 14942.17,
        "difficulty": 47.09,
        "effort": 703618.13,
        "level": 0.02,
        "bugs": 4.98,
        "time": 39090,
        "intelligentContent": 317.31,
        "number_operators": 412,
        "number_operands": 1448,
        "number_operators_unique": 16,
        "number_operands_unique": 246,
        "cloc": 143,
        "loc": 701,
        "lloc": 558,
        "mi": 32.2,
        "mIwoC": 0,
        "commentWeight": 32.2,
        "kanDefect": 6.42,
        "relativeStructuralComplexity": 1521,
        "relativeDataComplexity": 0.37,
        "relativeSystemComplexity": 1521.37,
        "totalStructuralComplexity": 19773,
        "totalDataComplexity": 4.83,
        "totalSystemComplexity": 19777.83,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 11,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "DoctorsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "show",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "bySpecialization",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 4,
        "nbMethods": 4,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 9,
        "ccn": 6,
        "ccnMethodMax": 4,
        "externals": [
            "WebController",
            "UserModel",
            "DoctorModel"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 69,
        "vocabulary": 22,
        "volume": 307.7,
        "difficulty": 12.6,
        "effort": 3877.03,
        "level": 0.08,
        "bugs": 0.1,
        "time": 215,
        "intelligentContent": 24.42,
        "number_operators": 15,
        "number_operands": 54,
        "number_operators_unique": 7,
        "number_operands_unique": 15,
        "cloc": 13,
        "loc": 56,
        "lloc": 43,
        "mi": 80.09,
        "mIwoC": 46.14,
        "commentWeight": 33.95,
        "kanDefect": 0.22,
        "relativeStructuralComplexity": 36,
        "relativeDataComplexity": 0.21,
        "relativeSystemComplexity": 36.21,
        "totalStructuralComplexity": 144,
        "totalDataComplexity": 0.86,
        "totalSystemComplexity": 144.86,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "FollowUpController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "show",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "delete",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "upcoming",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFollowUps",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "statistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 12,
        "nbMethods": 12,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 12,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 82,
        "ccn": 71,
        "ccnMethodMax": 16,
        "externals": [
            "WebController",
            "FollowUpModel",
            "ConsultationModel",
            "PatientModel",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 598,
        "vocabulary": 107,
        "volume": 4031.4,
        "difficulty": 18.83,
        "effort": 75904.29,
        "level": 0.05,
        "bugs": 1.34,
        "time": 4217,
        "intelligentContent": 214.11,
        "number_operators": 132,
        "number_operands": 466,
        "number_operators_unique": 8,
        "number_operands_unique": 99,
        "cloc": 53,
        "loc": 268,
        "lloc": 215,
        "mi": 46.11,
        "mIwoC": 14.33,
        "commentWeight": 31.79,
        "kanDefect": 2.11,
        "relativeStructuralComplexity": 324,
        "relativeDataComplexity": 1.18,
        "relativeSystemComplexity": 325.18,
        "totalStructuralComplexity": 3888,
        "totalDataComplexity": 14.16,
        "totalSystemComplexity": 3902.16,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 5,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "InsuranceController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getPolicy",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "savePolicy",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deletePolicy",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "calculateCoverage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientPolicies",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 5,
        "nbMethods": 5,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 5,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 60,
        "ccn": 56,
        "ccnMethodMax": 16,
        "externals": [
            "ApiController",
            "Exception"
        ],
        "parents": [
            "ApiController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 623,
        "vocabulary": 139,
        "volume": 4435.1,
        "difficulty": 27.1,
        "effort": 120176.91,
        "level": 0.04,
        "bugs": 1.48,
        "time": 6676,
        "intelligentContent": 163.68,
        "number_operators": 175,
        "number_operands": 448,
        "number_operators_unique": 15,
        "number_operands_unique": 124,
        "cloc": 65,
        "loc": 318,
        "lloc": 253,
        "mi": 46.74,
        "mIwoC": 14.51,
        "commentWeight": 32.23,
        "kanDefect": 2.46,
        "relativeStructuralComplexity": 361,
        "relativeDataComplexity": 1.3,
        "relativeSystemComplexity": 362.3,
        "totalStructuralComplexity": 1805,
        "totalDataComplexity": 6.5,
        "totalSystemComplexity": 1811.5,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 2,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "MedicationsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getMedications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedication",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "saveMedication",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "toggleStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStock",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 5,
        "nbMethods": 5,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 5,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 65,
        "ccn": 61,
        "ccnMethodMax": 22,
        "externals": [
            "ApiController",
            "Exception",
            "Exception"
        ],
        "parents": [
            "ApiController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 852,
        "vocabulary": 172,
        "volume": 6327.18,
        "difficulty": 31.19,
        "effort": 197371.67,
        "level": 0.03,
        "bugs": 2.11,
        "time": 10965,
        "intelligentContent": 202.83,
        "number_operators": 199,
        "number_operands": 653,
        "number_operators_unique": 15,
        "number_operands_unique": 157,
        "cloc": 70,
        "loc": 399,
        "lloc": 329,
        "mi": 40.48,
        "mIwoC": 10.27,
        "commentWeight": 30.21,
        "kanDefect": 2.76,
        "relativeStructuralComplexity": 324,
        "relativeDataComplexity": 1.37,
        "relativeSystemComplexity": 325.37,
        "totalStructuralComplexity": 1620,
        "totalDataComplexity": 6.84,
        "totalSystemComplexity": 1626.84,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 2,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "PaymentController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "show",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "delete",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "print",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPayments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "statistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 11,
        "nbMethods": 11,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 11,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 69,
        "ccn": 59,
        "ccnMethodMax": 16,
        "externals": [
            "WebController",
            "PaymentModel",
            "InvoiceModel",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 513,
        "vocabulary": 97,
        "volume": 3385.76,
        "difficulty": 20.56,
        "effort": 69600.36,
        "level": 0.05,
        "bugs": 1.13,
        "time": 3867,
        "intelligentContent": 164.7,
        "number_operators": 111,
        "number_operands": 402,
        "number_operators_unique": 9,
        "number_operands_unique": 88,
        "cloc": 48,
        "loc": 230,
        "lloc": 182,
        "mi": 50.55,
        "mIwoC": 18.05,
        "commentWeight": 32.51,
        "kanDefect": 1.76,
        "relativeStructuralComplexity": 196,
        "relativeDataComplexity": 1.3,
        "relativeSystemComplexity": 197.3,
        "totalStructuralComplexity": 2156,
        "totalDataComplexity": 14.27,
        "totalSystemComplexity": 2170.27,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 4,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "ValidationController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateUsername",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateEmail",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateAppointment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAvailableSlots",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAvailableDoctors",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 6,
        "nbMethods": 6,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 6,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 51,
        "ccn": 46,
        "ccnMethodMax": 15,
        "externals": [
            "WebController",
            "UserModel",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "DatabaseManager",
            "Exception",
            "Exception",
            "DateTime",
            "Exception",
            "DateTime",
            "Utilities",
            "Utilities",
            "DatabaseManager",
            "Exception",
            "Exception",
            "Utilities",
            "DatabaseManager",
            "Exception",
            "Exception",
            "DateTime",
            "Exception",
            "Utilities",
            "DatabaseManager",
            "Exception"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 399,
        "vocabulary": 90,
        "volume": 2590.25,
        "difficulty": 24.9,
        "effort": 64503.94,
        "level": 0.04,
        "bugs": 0.86,
        "time": 3584,
        "intelligentContent": 104.02,
        "number_operators": 104,
        "number_operands": 295,
        "number_operators_unique": 13,
        "number_operands_unique": 77,
        "cloc": 30,
        "loc": 195,
        "lloc": 165,
        "mi": 50.09,
        "mIwoC": 21.54,
        "commentWeight": 28.55,
        "kanDefect": 2.01,
        "relativeStructuralComplexity": 289,
        "relativeDataComplexity": 0.28,
        "relativeSystemComplexity": 289.28,
        "totalStructuralComplexity": 1734,
        "totalDataComplexity": 1.67,
        "totalSystemComplexity": 1735.67,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 6,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "BaseController",
        "interface": false,
        "abstract": true,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "render",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getBaseUrl",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirect",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getParam",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isLoggedIn",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCurrentUserId",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCurrentUserRole",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "hasRole",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 9,
        "nbMethods": 9,
        "nbMethodsPrivate": 8,
        "nbMethodsPublic": 1,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 14,
        "ccn": 6,
        "ccnMethodMax": 3,
        "externals": [
            "DatabaseManager",
            "Exception",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [],
        "implements": [],
        "lcom": 8,
        "length": 60,
        "vocabulary": 21,
        "volume": 263.54,
        "difficulty": 6.09,
        "effort": 1605.94,
        "level": 0.16,
        "bugs": 0.09,
        "time": 89,
        "intelligentContent": 43.25,
        "number_operators": 21,
        "number_operands": 39,
        "number_operators_unique": 5,
        "number_operands_unique": 16,
        "cloc": 50,
        "loc": 112,
        "lloc": 62,
        "mi": 86.14,
        "mIwoC": 43.14,
        "commentWeight": 43,
        "kanDefect": 0.43,
        "relativeStructuralComplexity": 25,
        "relativeDataComplexity": 1.78,
        "relativeSystemComplexity": 26.78,
        "totalStructuralComplexity": 225,
        "totalDataComplexity": 16,
        "totalSystemComplexity": 241,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 3,
        "instability": 0.6,
        "violations": {}
    },
    {
        "name": "ComponentsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "load",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleRequest",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFlashMessages",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 4,
        "nbMethods": 4,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 3,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 21,
        "ccn": 18,
        "ccnMethodMax": 15,
        "externals": [
            "SessionManager",
            "SessionManager"
        ],
        "parents": [],
        "implements": [],
        "lcom": 2,
        "length": 103,
        "vocabulary": 46,
        "volume": 568.93,
        "difficulty": 8.39,
        "effort": 4774.37,
        "level": 0.12,
        "bugs": 0.19,
        "time": 265,
        "intelligentContent": 67.79,
        "number_operators": 34,
        "number_operands": 69,
        "number_operators_unique": 9,
        "number_operands_unique": 37,
        "cloc": 30,
        "loc": 92,
        "lloc": 62,
        "mi": 77.87,
        "mIwoC": 39.19,
        "commentWeight": 38.68,
        "kanDefect": 0.78,
        "relativeStructuralComplexity": 16,
        "relativeDataComplexity": 0.7,
        "relativeSystemComplexity": 16.7,
        "totalStructuralComplexity": 64,
        "totalDataComplexity": 2.8,
        "totalSystemComplexity": 66.8,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 1,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "AuthController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showLogin",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processLogin",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logout",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showRegister",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processRegister",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateRegistrationData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showPatientRegister",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processPatientRegister",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showForgotPassword",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processForgotPassword",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showResetPassword",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processResetPassword",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendPasswordResetEmail",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 14,
        "nbMethods": 14,
        "nbMethodsPrivate": 2,
        "nbMethodsPublic": 12,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 114,
        "ccn": 101,
        "ccnMethodMax": 34,
        "externals": [
            "WebController",
            "Auth",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Auth",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "UserModel",
            "PatientModel",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "UserModel",
            "Auth",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "UserModel",
            "Exception",
            "Exception",
            "PatientModel",
            "Exception",
            "Auth",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Auth",
            "Exception",
            "Exception",
            "UserModel",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Auth",
            "UserModel",
            "SessionManager",
            "SessionManager",
            "Exception",
            "Exception",
            "Exception",
            "UserModel",
            "Exception",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 2,
        "length": 858,
        "vocabulary": 162,
        "volume": 6297.59,
        "difficulty": 26.32,
        "effort": 165752.6,
        "level": 0.04,
        "bugs": 2.1,
        "time": 9208,
        "intelligentContent": 239.27,
        "number_operators": 200,
        "number_operands": 658,
        "number_operators_unique": 12,
        "number_operands_unique": 150,
        "cloc": 154,
        "loc": 544,
        "lloc": 390,
        "mi": 39.99,
        "mIwoC": 3.29,
        "commentWeight": 36.7,
        "kanDefect": 4.11,
        "relativeStructuralComplexity": 900,
        "relativeDataComplexity": 0.43,
        "relativeSystemComplexity": 900.43,
        "totalStructuralComplexity": 12600,
        "totalDataComplexity": 6.03,
        "totalSystemComplexity": 12606.03,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 7,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "DashboardController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientLabResults",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 3,
        "nbMethods": 3,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 2,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 16,
        "ccn": 14,
        "ccnMethodMax": 10,
        "externals": [
            "WebController",
            "UserModel",
            "PatientModel",
            "AppointmentModel",
            "LabTestModel",
            "PrescriptionModel",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 324,
        "vocabulary": 74,
        "volume": 2011.86,
        "difficulty": 17.93,
        "effort": 36074.25,
        "level": 0.06,
        "bugs": 0.67,
        "time": 2004,
        "intelligentContent": 112.2,
        "number_operators": 65,
        "number_operands": 259,
        "number_operators_unique": 9,
        "number_operands_unique": 65,
        "cloc": 46,
        "loc": 158,
        "lloc": 112,
        "mi": 67.38,
        "mIwoC": 30.28,
        "commentWeight": 37.1,
        "kanDefect": 0.88,
        "relativeStructuralComplexity": 1156,
        "relativeDataComplexity": 0.13,
        "relativeSystemComplexity": 1156.13,
        "totalStructuralComplexity": 3468,
        "totalDataComplexity": 0.4,
        "totalSystemComplexity": 3468.4,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 7,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "DepartmentController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "show",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "delete",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "search",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDepartments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 10,
        "nbMethods": 10,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 10,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 43,
        "ccn": 34,
        "ccnMethodMax": 10,
        "externals": [
            "WebController",
            "DepartmentModel",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 322,
        "vocabulary": 54,
        "volume": 1853.07,
        "difficulty": 15.81,
        "effort": 29301.73,
        "level": 0.06,
        "bugs": 0.62,
        "time": 1628,
        "intelligentContent": 117.19,
        "number_operators": 69,
        "number_operands": 253,
        "number_operators_unique": 6,
        "number_operands_unique": 48,
        "cloc": 45,
        "loc": 212,
        "lloc": 167,
        "mi": 56.79,
        "mIwoC": 24.06,
        "commentWeight": 32.73,
        "kanDefect": 1.76,
        "relativeStructuralComplexity": 100,
        "relativeDataComplexity": 1.85,
        "relativeSystemComplexity": 101.85,
        "totalStructuralComplexity": 1000,
        "totalDataComplexity": 18.55,
        "totalSystemComplexity": 1018.55,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "ErrorController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "unauthorized",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 2,
        "nbMethods": 2,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 2,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 2,
        "ccn": 1,
        "ccnMethodMax": 1,
        "externals": [
            "WebController"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 2,
        "length": 7,
        "vocabulary": 6,
        "volume": 18.09,
        "difficulty": 0.6,
        "effort": 10.86,
        "level": 1.67,
        "bugs": 0.01,
        "time": 1,
        "intelligentContent": 30.16,
        "number_operators": 1,
        "number_operands": 6,
        "number_operators_unique": 1,
        "number_operands_unique": 5,
        "cloc": 9,
        "loc": 25,
        "lloc": 16,
        "mi": 104.86,
        "mIwoC": 64.79,
        "commentWeight": 40.07,
        "kanDefect": 0.15,
        "relativeStructuralComplexity": 4,
        "relativeDataComplexity": 0,
        "relativeSystemComplexity": 4,
        "totalStructuralComplexity": 8,
        "totalDataComplexity": 0,
        "totalSystemComplexity": 8,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 1,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "GuestAppointmentController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showBookingForm",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "bookAppointment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "confirmation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getServices",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createPatientOptimized",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createAppointmentOptimized",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generatePatientNumber",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "queueConfirmationEmail",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendConfirmationEmail",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isAjaxRequest",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 11,
        "nbMethods": 11,
        "nbMethodsPrivate": 7,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 43,
        "ccn": 33,
        "ccnMethodMax": 24,
        "externals": [
            "WebController",
            "AppointmentModel",
            "PatientModel",
            "UserModel",
            "NotificationService",
            "Exception",
            "Exception",
            "Exception",
            "DateTime",
            "Exception",
            "DateTime",
            "Exception",
            "Utilities",
            "Utilities",
            "Exception",
            "DatabaseManager",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "Exception"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 2,
        "length": 510,
        "vocabulary": 158,
        "volume": 3724.93,
        "difficulty": 19.4,
        "effort": 72248.09,
        "level": 0.05,
        "bugs": 1.24,
        "time": 4014,
        "intelligentContent": 192.05,
        "number_operators": 111,
        "number_operands": 399,
        "number_operators_unique": 14,
        "number_operands_unique": 144,
        "cloc": 65,
        "loc": 262,
        "lloc": 197,
        "mi": 55.37,
        "mIwoC": 20.5,
        "commentWeight": 34.87,
        "kanDefect": 1.78,
        "relativeStructuralComplexity": 2025,
        "relativeDataComplexity": 0.14,
        "relativeSystemComplexity": 2025.14,
        "totalStructuralComplexity": 22275,
        "totalDataComplexity": 1.57,
        "totalSystemComplexity": 22276.57,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 9,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "HomeController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "services",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "obstetricsServices",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "gynecologyServices",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "laboratoryServices",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "pharmacyServices",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "about",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "contact",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendContact",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "subscribeNewsletter",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectToFacebook",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectToTwitter",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectToInstagram",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectToLinkedIn",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getServices",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getObstetricsService",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getGynecologyService",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getLaboratoryService",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPharmacyService",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendContactEmail",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "storeNewsletterSubscription",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 22,
        "nbMethods": 22,
        "nbMethodsPrivate": 7,
        "nbMethodsPublic": 15,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 29,
        "ccn": 8,
        "ccnMethodMax": 5,
        "externals": [
            "WebController",
            "Auth",
            "Exception",
            "Exception",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "Exception",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 8,
        "length": 188,
        "vocabulary": 91,
        "volume": 1223.47,
        "difficulty": 4.85,
        "effort": 5939.5,
        "level": 0.21,
        "bugs": 0.41,
        "time": 330,
        "intelligentContent": 252.02,
        "number_operators": 21,
        "number_operands": 167,
        "number_operators_unique": 5,
        "number_operands_unique": 86,
        "cloc": 127,
        "loc": 269,
        "lloc": 142,
        "mi": 74.08,
        "mIwoC": 30.35,
        "commentWeight": 43.73,
        "kanDefect": 0.66,
        "relativeStructuralComplexity": 225,
        "relativeDataComplexity": 0.32,
        "relativeSystemComplexity": 225.32,
        "totalStructuralComplexity": 4950,
        "totalDataComplexity": 7,
        "totalSystemComplexity": 4957,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 4,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "InvoiceController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "show",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "print",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllInvoices",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatients",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctors",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getServices",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 12,
        "nbMethods": 12,
        "nbMethodsPrivate": 4,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 65,
        "ccn": 54,
        "ccnMethodMax": 15,
        "externals": [
            "WebController",
            "InvoiceModel",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 506,
        "vocabulary": 92,
        "volume": 3300.92,
        "difficulty": 25.4,
        "effort": 83827.13,
        "level": 0.04,
        "bugs": 1.1,
        "time": 4657,
        "intelligentContent": 129.98,
        "number_operators": 132,
        "number_operands": 374,
        "number_operators_unique": 11,
        "number_operands_unique": 81,
        "cloc": 49,
        "loc": 271,
        "lloc": 222,
        "mi": 47.52,
        "mIwoC": 16.92,
        "commentWeight": 30.61,
        "kanDefect": 2.06,
        "relativeStructuralComplexity": 361,
        "relativeDataComplexity": 1.17,
        "relativeSystemComplexity": 362.17,
        "totalStructuralComplexity": 4332,
        "totalDataComplexity": 14.05,
        "totalSystemComplexity": 4346.05,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "LabRequestController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "initCache",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFilteredRequests",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientIdForCurrentUser",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getValidatedStatus",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getValidPerPage",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sanitizeInput",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logPerformance",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logSecurityEvent",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getClientIp",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "checkRateLimit",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRateLimitKey",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRateLimitCount",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "incrementRateLimit",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "checkCsrfToken",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isAjaxRequest",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCsrfToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateCsrfToken",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isDebugMode",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "saveResults",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "printResults",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "canViewRequest",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "canEditRequest",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "canDeleteRequest",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logAction",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectWithError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 32,
        "nbMethods": 32,
        "nbMethodsPrivate": 23,
        "nbMethodsPublic": 9,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 153,
        "ccn": 122,
        "ccnMethodMax": 20,
        "externals": [
            "WebController",
            "SessionManager",
            "SessionManager",
            "LabTestModel",
            "PatientModel",
            "UserModel",
            "AuditLogger",
            "Exception",
            "Exception",
            "Exception",
            "Exception"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 3,
        "length": 1179,
        "vocabulary": 207,
        "volume": 9070.62,
        "difficulty": 37.11,
        "effort": 336610.27,
        "level": 0.03,
        "bugs": 3.02,
        "time": 18701,
        "intelligentContent": 244.43,
        "number_operators": 293,
        "number_operands": 886,
        "number_operators_unique": 16,
        "number_operands_unique": 191,
        "cloc": 172,
        "loc": 637,
        "lloc": 465,
        "mi": 36.04,
        "mIwoC": 0,
        "commentWeight": 36.04,
        "kanDefect": 5.36,
        "relativeStructuralComplexity": 2809,
        "relativeDataComplexity": 0.87,
        "relativeSystemComplexity": 2809.87,
        "totalStructuralComplexity": 89888,
        "totalDataComplexity": 27.76,
        "totalSystemComplexity": 89915.76,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 7,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "LabResultsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "download",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getTestRequestResults",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientLabResults",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllLabResults",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 7,
        "nbMethods": 7,
        "nbMethodsPrivate": 3,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 31,
        "ccn": 25,
        "ccnMethodMax": 7,
        "externals": [
            "WebController",
            "PatientModel",
            "LabTestModel",
            "UserModel",
            "DatabaseManager",
            "Exception",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "DatabaseManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 320,
        "vocabulary": 57,
        "volume": 1866.52,
        "difficulty": 20.81,
        "effort": 38847.05,
        "level": 0.05,
        "bugs": 0.62,
        "time": 2158,
        "intelligentContent": 89.68,
        "number_operators": 98,
        "number_operands": 222,
        "number_operators_unique": 9,
        "number_operands_unique": 48,
        "cloc": 32,
        "loc": 198,
        "lloc": 166,
        "mi": 54.47,
        "mIwoC": 25.3,
        "commentWeight": 29.17,
        "kanDefect": 1.75,
        "relativeStructuralComplexity": 441,
        "relativeDataComplexity": 0.71,
        "relativeSystemComplexity": 441.71,
        "totalStructuralComplexity": 3087,
        "totalDataComplexity": 4.95,
        "totalSystemComplexity": 3091.95,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 8,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "MessagesController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "compose",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "show",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "send",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "archive",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "delete",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "search",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 9,
        "nbMethods": 9,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 9,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 58,
        "ccn": 50,
        "ccnMethodMax": 11,
        "externals": [
            "WebController",
            "MessageModel",
            "UserModel"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 493,
        "vocabulary": 96,
        "volume": 3246.39,
        "difficulty": 32.35,
        "effort": 105032.48,
        "level": 0.03,
        "bugs": 1.08,
        "time": 5835,
        "intelligentContent": 100.34,
        "number_operators": 114,
        "number_operands": 379,
        "number_operators_unique": 14,
        "number_operands_unique": 82,
        "cloc": 60,
        "loc": 260,
        "lloc": 200,
        "mi": 52.36,
        "mIwoC": 18.49,
        "commentWeight": 33.87,
        "kanDefect": 1.77,
        "relativeStructuralComplexity": 289,
        "relativeDataComplexity": 0.67,
        "relativeSystemComplexity": 289.67,
        "totalStructuralComplexity": 2601,
        "totalDataComplexity": 6.06,
        "totalSystemComplexity": 2607.06,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "NotificationsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAllAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 4,
        "nbMethods": 4,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 16,
        "ccn": 13,
        "ccnMethodMax": 6,
        "externals": [
            "WebController",
            "NotificationModel",
            "UserModel",
            "NotificationService",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 186,
        "vocabulary": 54,
        "volume": 1070.41,
        "difficulty": 19.57,
        "effort": 20949.43,
        "level": 0.05,
        "bugs": 0.36,
        "time": 1164,
        "intelligentContent": 54.69,
        "number_operators": 49,
        "number_operands": 137,
        "number_operators_unique": 12,
        "number_operands_unique": 42,
        "cloc": 17,
        "loc": 96,
        "lloc": 79,
        "mi": 65.98,
        "mIwoC": 35.64,
        "commentWeight": 30.34,
        "kanDefect": 0.57,
        "relativeStructuralComplexity": 144,
        "relativeDataComplexity": 0.77,
        "relativeSystemComplexity": 144.77,
        "totalStructuralComplexity": 576,
        "totalDataComplexity": 3.08,
        "totalSystemComplexity": 579.08,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 5,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "PatientController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStatusClass",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientLabResults",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 10,
        "nbMethods": 10,
        "nbMethodsPrivate": 2,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 66,
        "ccn": 57,
        "ccnMethodMax": 16,
        "externals": [
            "WebController",
            "PatientModel",
            "AppointmentModel",
            "MedicalHistoryModel",
            "ConsultationModel",
            "VitalSignModel",
            "PrescriptionModel",
            "MedicalHistoryModel",
            "SessionManager",
            "MedicalHistoryModel"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 661,
        "vocabulary": 157,
        "volume": 4821.74,
        "difficulty": 19.29,
        "effort": 93000.22,
        "level": 0.05,
        "bugs": 1.61,
        "time": 5167,
        "intelligentContent": 249.99,
        "number_operators": 149,
        "number_operands": 512,
        "number_operators_unique": 11,
        "number_operands_unique": 146,
        "cloc": 80,
        "loc": 321,
        "lloc": 241,
        "mi": 49.51,
        "mIwoC": 14.58,
        "commentWeight": 34.93,
        "kanDefect": 2.27,
        "relativeStructuralComplexity": 900,
        "relativeDataComplexity": 0.63,
        "relativeSystemComplexity": 900.63,
        "totalStructuralComplexity": 9000,
        "totalDataComplexity": 6.32,
        "totalSystemComplexity": 9006.32,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 8,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "PharmacyController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "medicines",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createMedicine",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "storeMedicine",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showMedicine",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "editMedicine",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateMedicine",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "toggleMedicineStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "inventory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "orders",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "renderUnderDevelopment",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 11,
        "nbMethods": 11,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 10,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 53,
        "ccn": 43,
        "ccnMethodMax": 14,
        "externals": [
            "WebController",
            "MedicationModel"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 505,
        "vocabulary": 103,
        "volume": 3376.68,
        "difficulty": 15.24,
        "effort": 51459.24,
        "level": 0.07,
        "bugs": 1.13,
        "time": 2859,
        "intelligentContent": 221.57,
        "number_operators": 87,
        "number_operands": 418,
        "number_operators_unique": 7,
        "number_operands_unique": 96,
        "cloc": 44,
        "loc": 220,
        "lloc": 176,
        "mi": 52.46,
        "mIwoC": 20.53,
        "commentWeight": 31.94,
        "kanDefect": 1.38,
        "relativeStructuralComplexity": 529,
        "relativeDataComplexity": 0.31,
        "relativeSystemComplexity": 529.31,
        "totalStructuralComplexity": 5819,
        "totalDataComplexity": 3.42,
        "totalSystemComplexity": 5822.42,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 2,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "PrescriptionController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "print",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "pending",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "dispense",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cancel",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "canViewPrescription",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "canCancelPrescription",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFrequencies",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDurations",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 13,
        "nbMethods": 13,
        "nbMethodsPrivate": 4,
        "nbMethodsPublic": 9,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 87,
        "ccn": 75,
        "ccnMethodMax": 21,
        "externals": [
            "WebController",
            "PrescriptionModel",
            "PatientModel",
            "MedicationModel",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 785,
        "vocabulary": 162,
        "volume": 5761.78,
        "difficulty": 21.71,
        "effort": 125080.28,
        "level": 0.05,
        "bugs": 1.92,
        "time": 6949,
        "intelligentContent": 265.41,
        "number_operators": 189,
        "number_operands": 596,
        "number_operators_unique": 11,
        "number_operands_unique": 151,
        "cloc": 98,
        "loc": 406,
        "lloc": 308,
        "mi": 43.78,
        "mIwoC": 9.3,
        "commentWeight": 34.49,
        "kanDefect": 2.92,
        "relativeStructuralComplexity": 729,
        "relativeDataComplexity": 0.99,
        "relativeSystemComplexity": 729.99,
        "totalStructuralComplexity": 9477,
        "totalDataComplexity": 12.82,
        "totalSystemComplexity": 9489.82,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 6,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "ReportsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "appointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "patients",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "financial",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "laboratory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "pharmacy",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getSystemStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 8,
        "nbMethods": 8,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 7,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 21,
        "ccn": 14,
        "ccnMethodMax": 14,
        "externals": [
            "WebController"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 223,
        "vocabulary": 51,
        "volume": 1264.95,
        "difficulty": 10.43,
        "effort": 13199.49,
        "level": 0.1,
        "bugs": 0.42,
        "time": 733,
        "intelligentContent": 121.22,
        "number_operators": 31,
        "number_operands": 192,
        "number_operators_unique": 5,
        "number_operands_unique": 46,
        "cloc": 47,
        "loc": 117,
        "lloc": 70,
        "mi": 77.72,
        "mIwoC": 36.15,
        "commentWeight": 41.58,
        "kanDefect": 0.22,
        "relativeStructuralComplexity": 36,
        "relativeDataComplexity": 0.43,
        "relativeSystemComplexity": 36.43,
        "totalStructuralComplexity": 288,
        "totalDataComplexity": 3.43,
        "totalSystemComplexity": 291.43,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 1,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "SettingsController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "users",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "system",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "database",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getSystemStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 6,
        "nbMethods": 6,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 5,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 13,
        "ccn": 8,
        "ccnMethodMax": 8,
        "externals": [
            "WebController"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 129,
        "vocabulary": 38,
        "volume": 676.98,
        "difficulty": 8.26,
        "effort": 5590.24,
        "level": 0.12,
        "bugs": 0.23,
        "time": 311,
        "intelligentContent": 81.98,
        "number_operators": 20,
        "number_operands": 109,
        "number_operators_unique": 5,
        "number_operands_unique": 33,
        "cloc": 35,
        "loc": 86,
        "lloc": 51,
        "mi": 83.61,
        "mIwoC": 41.86,
        "commentWeight": 41.75,
        "kanDefect": 0.22,
        "relativeStructuralComplexity": 36,
        "relativeDataComplexity": 0.43,
        "relativeSystemComplexity": 36.43,
        "totalStructuralComplexity": 216,
        "totalDataComplexity": 2.57,
        "totalSystemComplexity": 218.57,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 1,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "UserController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "index",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "edit",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "delete",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "profile",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "editProfile",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateProfile",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "changePasswordForm",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "changePassword",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validateUserData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 14,
        "nbMethods": 14,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 13,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 91,
        "ccn": 78,
        "ccnMethodMax": 25,
        "externals": [
            "WebController",
            "UserModel",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 797,
        "vocabulary": 143,
        "volume": 5706.42,
        "difficulty": 22.89,
        "effort": 130646.93,
        "level": 0.04,
        "bugs": 1.9,
        "time": 7258,
        "intelligentContent": 249.25,
        "number_operators": 188,
        "number_operands": 609,
        "number_operators_unique": 10,
        "number_operands_unique": 133,
        "cloc": 133,
        "loc": 485,
        "lloc": 352,
        "mi": 43.91,
        "mIwoC": 7.66,
        "commentWeight": 36.26,
        "kanDefect": 3.37,
        "relativeStructuralComplexity": 529,
        "relativeDataComplexity": 1.14,
        "relativeSystemComplexity": 530.14,
        "totalStructuralComplexity": 7406,
        "totalDataComplexity": 16,
        "totalSystemComplexity": 7422,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 5,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "VitalSignController",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "store",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "view",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "history",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logAction",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectWithError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectWithSuccess",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 9,
        "nbMethods": 9,
        "nbMethodsPrivate": 4,
        "nbMethodsPublic": 5,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 68,
        "ccn": 60,
        "ccnMethodMax": 43,
        "externals": [
            "WebController",
            "VitalSignModel",
            "PatientModel",
            "UserModel",
            "ErrorHandler"
        ],
        "parents": [
            "WebController"
        ],
        "implements": [],
        "lcom": 1,
        "length": 467,
        "vocabulary": 111,
        "volume": 3172.99,
        "difficulty": 23.68,
        "effort": 75131.92,
        "level": 0.04,
        "bugs": 1.06,
        "time": 4174,
        "intelligentContent": 134,
        "number_operators": 110,
        "number_operands": 357,
        "number_operators_unique": 13,
        "number_operands_unique": 98,
        "cloc": 58,
        "loc": 229,
        "lloc": 171,
        "mi": 53.85,
        "mIwoC": 18.7,
        "commentWeight": 35.15,
        "kanDefect": 2.02,
        "relativeStructuralComplexity": 289,
        "relativeDataComplexity": 0.57,
        "relativeSystemComplexity": 289.57,
        "totalStructuralComplexity": 2601,
        "totalDataComplexity": 5.11,
        "totalSystemComplexity": 2606.11,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 5,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "WebController",
        "interface": false,
        "abstract": true,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "run",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "setFlashMessage",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFlashMessages",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "initFlashMessages",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "renderView",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "showError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleException",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleFormError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isDebugMode",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDefaultData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "determineActiveMenu",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirect",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectToRoute",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "processFormData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getBaseUrl",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectWithError",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectWithSuccess",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "jsonResponse",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 20,
        "nbMethods": 20,
        "nbMethodsPrivate": 18,
        "nbMethodsPublic": 2,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 72,
        "ccn": 53,
        "ccnMethodMax": 17,
        "externals": [
            "BaseController",
            "Auth",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "WebRouter",
            "ErrorHandler"
        ],
        "parents": [
            "BaseController"
        ],
        "implements": [],
        "lcom": 2,
        "length": 487,
        "vocabulary": 129,
        "volume": 3414.47,
        "difficulty": 20.12,
        "effort": 68686.73,
        "level": 0.05,
        "bugs": 1.14,
        "time": 3816,
        "intelligentContent": 169.74,
        "number_operators": 128,
        "number_operands": 359,
        "number_operators_unique": 13,
        "number_operands_unique": 116,
        "cloc": 168,
        "loc": 411,
        "lloc": 243,
        "mi": 57.91,
        "mIwoC": 16.09,
        "commentWeight": 41.81,
        "kanDefect": 2.13,
        "relativeStructuralComplexity": 625,
        "relativeDataComplexity": 0.74,
        "relativeSystemComplexity": 625.74,
        "totalStructuralComplexity": 12500,
        "totalDataComplexity": 14.88,
        "totalSystemComplexity": 12514.88,
        "package": "\\",
        "pageRank": 0.06,
        "afferentCoupling": 25,
        "efferentCoupling": 5,
        "instability": 0.17,
        "violations": {}
    },
    {
        "name": "Auth",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInstance",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isLoggedIn",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "hasRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "hasAnyRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "authenticate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "checkRememberToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logout",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectByRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "redirectToLogin",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "requireLogin",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "requireRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getBaseUrl",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 15,
        "nbMethods": 15,
        "nbMethodsPrivate": 3,
        "nbMethodsPublic": 12,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 35,
        "ccn": 21,
        "ccnMethodMax": 5,
        "externals": [
            "DatabaseManager",
            "UserModel",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "ErrorHandler",
            "SessionManager",
            "ErrorHandler",
            "SessionManager",
            "SessionManager",
            "SessionManager",
            "SessionManager"
        ],
        "parents": [],
        "implements": [],
        "lcom": 4,
        "length": 204,
        "vocabulary": 46,
        "volume": 1126.81,
        "difficulty": 19.44,
        "effort": 21910.13,
        "level": 0.05,
        "bugs": 0.38,
        "time": 1217,
        "intelligentContent": 57.95,
        "number_operators": 64,
        "number_operands": 140,
        "number_operators_unique": 10,
        "number_operands_unique": 36,
        "cloc": 90,
        "loc": 246,
        "lloc": 156,
        "mi": 68.26,
        "mIwoC": 27.97,
        "commentWeight": 40.29,
        "kanDefect": 1.34,
        "relativeStructuralComplexity": 289,
        "relativeDataComplexity": 0.97,
        "relativeSystemComplexity": 289.97,
        "totalStructuralComplexity": 4335,
        "totalDataComplexity": 14.61,
        "totalSystemComplexity": 4349.61,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 3,
        "efferentCoupling": 4,
        "instability": 0.57,
        "violations": {}
    },
    {
        "name": "DatabaseManager",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "loadEnv",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInstance",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getConnection",
                "role": "getter",
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "__clone",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "__destruct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 6,
        "nbMethods": 5,
        "nbMethodsPrivate": 3,
        "nbMethodsPublic": 2,
        "nbMethodsGetter": 1,
        "nbMethodsSetters": 0,
        "wmc": 9,
        "ccn": 5,
        "ccnMethodMax": 3,
        "externals": [
            "mysqli",
            "Exception",
            "Dotenv\\Dotenv"
        ],
        "parents": [],
        "implements": [],
        "lcom": 3,
        "length": 41,
        "vocabulary": 18,
        "volume": 170.97,
        "difficulty": 5.38,
        "effort": 920.59,
        "level": 0.19,
        "bugs": 0.06,
        "time": 51,
        "intelligentContent": 31.75,
        "number_operators": 13,
        "number_operands": 28,
        "number_operators_unique": 5,
        "number_operands_unique": 13,
        "cloc": 30,
        "loc": 74,
        "lloc": 44,
        "mi": 89.54,
        "mIwoC": 47.84,
        "commentWeight": 41.7,
        "kanDefect": 0.43,
        "relativeStructuralComplexity": 25,
        "relativeDataComplexity": 0.33,
        "relativeSystemComplexity": 25.33,
        "totalStructuralComplexity": 150,
        "totalDataComplexity": 2,
        "totalSystemComplexity": 152,
        "package": "\\",
        "pageRank": 0.02,
        "afferentCoupling": 6,
        "efferentCoupling": 3,
        "instability": 0.33,
        "violations": {}
    },
    {
        "name": "ErrorHandler",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "log",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logDatabaseError",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logSystemError",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "logApiError",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 4,
        "nbMethods": 4,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 8,
        "ccn": 5,
        "ccnMethodMax": 3,
        "externals": [],
        "parents": [],
        "implements": [],
        "lcom": 4,
        "length": 83,
        "vocabulary": 28,
        "volume": 399.01,
        "difficulty": 5.25,
        "effort": 2094.8,
        "level": 0.19,
        "bugs": 0.13,
        "time": 116,
        "intelligentContent": 76,
        "number_operators": 20,
        "number_operands": 63,
        "number_operators_unique": 4,
        "number_operands_unique": 24,
        "cloc": 34,
        "loc": 77,
        "lloc": 43,
        "mi": 88.33,
        "mIwoC": 45.48,
        "commentWeight": 42.85,
        "kanDefect": 0.43,
        "relativeStructuralComplexity": 4,
        "relativeDataComplexity": 0.83,
        "relativeSystemComplexity": 4.83,
        "totalStructuralComplexity": 16,
        "totalDataComplexity": 3.33,
        "totalSystemComplexity": 19.33,
        "package": "\\",
        "pageRank": 0.27,
        "afferentCoupling": 32,
        "efferentCoupling": 0,
        "instability": 0,
        "violations": {}
    },
    {
        "name": "Router",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "register",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "dispatch",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generateUrl",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleException",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 4,
        "nbMethods": 4,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 14,
        "ccn": 11,
        "ccnMethodMax": 7,
        "externals": [
            "Exception",
            "controllerName",
            "Exception",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [],
        "implements": [],
        "lcom": 2,
        "length": 137,
        "vocabulary": 42,
        "volume": 738.75,
        "difficulty": 9,
        "effort": 6648.73,
        "level": 0.11,
        "bugs": 0.25,
        "time": 369,
        "intelligentContent": 82.08,
        "number_operators": 29,
        "number_operands": 108,
        "number_operators_unique": 6,
        "number_operands_unique": 36,
        "cloc": 45,
        "loc": 117,
        "lloc": 72,
        "mi": 78.9,
        "mIwoC": 37.92,
        "commentWeight": 40.98,
        "kanDefect": 1.1,
        "relativeStructuralComplexity": 25,
        "relativeDataComplexity": 1.46,
        "relativeSystemComplexity": 26.46,
        "totalStructuralComplexity": 100,
        "totalDataComplexity": 5.83,
        "totalSystemComplexity": 105.83,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "SessionManager",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "ensureStarted",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "get",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "set",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "remove",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "has",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "destroy",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "all",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isLoggedIn",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 8,
        "nbMethods": 8,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 16,
        "ccn": 9,
        "ccnMethodMax": 6,
        "externals": [],
        "parents": [],
        "implements": [],
        "lcom": 8,
        "length": 51,
        "vocabulary": 15,
        "volume": 199.25,
        "difficulty": 8.5,
        "effort": 1693.64,
        "level": 0.12,
        "bugs": 0.07,
        "time": 94,
        "intelligentContent": 23.44,
        "number_operators": 17,
        "number_operands": 34,
        "number_operators_unique": 5,
        "number_operands_unique": 10,
        "cloc": 48,
        "loc": 106,
        "lloc": 58,
        "mi": 87.4,
        "mIwoC": 44.22,
        "commentWeight": 43.18,
        "kanDefect": 0.43,
        "relativeStructuralComplexity": 1,
        "relativeDataComplexity": 2.88,
        "relativeSystemComplexity": 3.88,
        "totalStructuralComplexity": 8,
        "totalDataComplexity": 23,
        "totalSystemComplexity": 31,
        "package": "\\",
        "pageRank": 0.2,
        "afferentCoupling": 21,
        "efferentCoupling": 0,
        "instability": 0,
        "violations": {}
    },
    {
        "name": "Utilities",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getOpeningWindowsForDate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isWithinOpeningHours",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "nextValidStart",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generateTimeSlots",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getBaseUrl",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sanitize",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "formatDate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generateToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isValidJson",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRouteUrl",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 10,
        "nbMethods": 10,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 10,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 33,
        "ccn": 24,
        "ccnMethodMax": 7,
        "externals": [
            "DateTime",
            "DateTime",
            "DateTime",
            "DateTime",
            "DateTime",
            "DateTime",
            "DateTime",
            "DateTime",
            "DateTime"
        ],
        "parents": [],
        "implements": [],
        "lcom": 10,
        "length": 241,
        "vocabulary": 68,
        "volume": 1467.08,
        "difficulty": 19.96,
        "effort": 29287.23,
        "level": 0.05,
        "bugs": 0.49,
        "time": 1627,
        "intelligentContent": 73.49,
        "number_operators": 87,
        "number_operands": 154,
        "number_operators_unique": 14,
        "number_operands_unique": 54,
        "cloc": 57,
        "loc": 175,
        "lloc": 118,
        "mi": 68.07,
        "mIwoC": 29.4,
        "commentWeight": 38.67,
        "kanDefect": 2.14,
        "relativeStructuralComplexity": 144,
        "relativeDataComplexity": 1.41,
        "relativeSystemComplexity": 145.41,
        "totalStructuralComplexity": 1440,
        "totalDataComplexity": 14.08,
        "totalSystemComplexity": 1454.08,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 1,
        "instability": 0.33,
        "violations": {}
    },
    {
        "name": "WebRouter",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getInstance",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "register",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "setNotFoundCallback",
                "role": "setter",
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "patternToRegex",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "dispatch",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "handleNotFound",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "findSimilarRoutes",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generateUrl",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 8,
        "nbMethods": 7,
        "nbMethodsPrivate": 3,
        "nbMethodsPublic": 4,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 1,
        "wmc": 49,
        "ccn": 43,
        "ccnMethodMax": 18,
        "externals": [
            "Exception",
            "Exception",
            "controllerName",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [],
        "implements": [],
        "lcom": 2,
        "length": 413,
        "vocabulary": 86,
        "volume": 2654.05,
        "difficulty": 26.45,
        "effort": 70186.83,
        "level": 0.04,
        "bugs": 0.88,
        "time": 3899,
        "intelligentContent": 100.36,
        "number_operators": 116,
        "number_operands": 297,
        "number_operators_unique": 13,
        "number_operands_unique": 73,
        "cloc": 82,
        "loc": 268,
        "lloc": 186,
        "mi": 58.53,
        "mIwoC": 20.74,
        "commentWeight": 37.79,
        "kanDefect": 3.21,
        "relativeStructuralComplexity": 16,
        "relativeDataComplexity": 2.55,
        "relativeSystemComplexity": 18.55,
        "totalStructuralComplexity": 128,
        "totalDataComplexity": 20.4,
        "totalSystemComplexity": 148.4,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 1,
        "efferentCoupling": 3,
        "instability": 0.75,
        "violations": {}
    },
    {
        "name": "AuditLogger",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "log",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getEntityLogs",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserLogs",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getLogs",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countLogs",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cleanupOldLogs",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 7,
        "nbMethods": 7,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 7,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 64,
        "ccn": 58,
        "ccnMethodMax": 15,
        "externals": [
            "Exception",
            "Exception",
            "Exception",
            "Exception"
        ],
        "parents": [],
        "implements": [],
        "lcom": 1,
        "length": 649,
        "vocabulary": 103,
        "volume": 4339.54,
        "difficulty": 32.5,
        "effort": 141035.01,
        "level": 0.03,
        "bugs": 1.45,
        "time": 7835,
        "intelligentContent": 133.52,
        "number_operators": 199,
        "number_operands": 450,
        "number_operators_unique": 13,
        "number_operands_unique": 90,
        "cloc": 68,
        "loc": 338,
        "lloc": 270,
        "mi": 45.71,
        "mIwoC": 13.69,
        "commentWeight": 32.01,
        "kanDefect": 3.38,
        "relativeStructuralComplexity": 49,
        "relativeDataComplexity": 1.68,
        "relativeSystemComplexity": 50.68,
        "totalStructuralComplexity": 343,
        "totalDataComplexity": 11.75,
        "totalSystemComplexity": 354.75,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 3,
        "efferentCoupling": 1,
        "instability": 0.25,
        "violations": {}
    },
    {
        "name": "DashboardHelper",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getDashboardStats",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAdminDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getNurseDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getLabTechnicianDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPharmacistDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDefaultDashboardStats",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCount",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getSum",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStaffIdByUserId",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientIdByUserId",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRecentActivities",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAdminRecentActivities",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorRecentActivities",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getNurseRecentActivities",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientRecentActivities",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 17,
        "nbMethods": 17,
        "nbMethodsPrivate": 15,
        "nbMethodsPublic": 2,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 45,
        "ccn": 29,
        "ccnMethodMax": 7,
        "externals": [],
        "parents": [],
        "implements": [],
        "lcom": 17,
        "length": 634,
        "vocabulary": 115,
        "volume": 4340.04,
        "difficulty": 20,
        "effort": 86780.34,
        "level": 0.05,
        "bugs": 1.45,
        "time": 4821,
        "intelligentContent": 217.05,
        "number_operators": 163,
        "number_operands": 471,
        "number_operators_unique": 9,
        "number_operands_unique": 106,
        "cloc": 135,
        "loc": 403,
        "lloc": 268,
        "mi": 56.72,
        "mIwoC": 17.66,
        "commentWeight": 39.06,
        "kanDefect": 2.21,
        "relativeStructuralComplexity": 441,
        "relativeDataComplexity": 1.02,
        "relativeSystemComplexity": 442.02,
        "totalStructuralComplexity": 7497,
        "totalDataComplexity": 17.36,
        "totalSystemComplexity": 7514.36,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 0,
        "instability": 0,
        "violations": {}
    },
    {
        "name": "AppointmentModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getDoctorAppointmentCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorAppointmentsByStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAppointmentsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStatusClass",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAppointmentDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAvailableTimeSlots",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createAppointment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateAppointment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cancelAppointment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUpcomingAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getTodayAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countAppointmentsByStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countDoctorAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countDoctorPatients",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllAppointmentsByDate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUpcomingAppointmentsByDateRange",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUpcomingDoctorAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPastPatientAppointments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getTotalAppointmentsCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCountByStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAppointmentsWithoutConsultation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isTimeSlotAvailable",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 27,
        "nbMethods": 27,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 26,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 128,
        "ccn": 102,
        "ccnMethodMax": 21,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "MedicalHistoryModel",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 2,
        "length": 1290,
        "vocabulary": 177,
        "volume": 9633.21,
        "difficulty": 49.57,
        "effort": 477476.13,
        "level": 0.02,
        "bugs": 3.21,
        "time": 26526,
        "intelligentContent": 194.35,
        "number_operators": 357,
        "number_operands": 933,
        "number_operators_unique": 17,
        "number_operands_unique": 160,
        "cloc": 225,
        "loc": 837,
        "lloc": 612,
        "mi": 35.98,
        "mIwoC": 0,
        "commentWeight": 35.98,
        "kanDefect": 5.5,
        "relativeStructuralComplexity": 484,
        "relativeDataComplexity": 2.46,
        "relativeSystemComplexity": 486.46,
        "totalStructuralComplexity": 13068,
        "totalDataComplexity": 66.48,
        "totalSystemComplexity": 13134.48,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 7,
        "efferentCoupling": 4,
        "instability": 0.36,
        "violations": {}
    },
    {
        "name": "BaseModel",
        "interface": false,
        "abstract": true,
        "final": false,
        "methods": [
            {
                "name": "getDbConnection",
                "role": "getter",
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getTableColumns",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "find",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "findAll",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "update",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "delete",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "count",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "beginTransaction",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "commitTransaction",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "rollbackTransaction",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "validate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getErrors",
                "role": "getter",
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRelated",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "query",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 16,
        "nbMethods": 14,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 14,
        "nbMethodsGetter": 2,
        "nbMethodsSetters": 0,
        "wmc": 98,
        "ccn": 85,
        "ccnMethodMax": 23,
        "externals": [
            "ErrorHandler",
            "DatabaseManager",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [],
        "implements": [],
        "lcom": 1,
        "length": 826,
        "vocabulary": 104,
        "volume": 5534.56,
        "difficulty": 45.19,
        "effort": 250100.76,
        "level": 0.02,
        "bugs": 1.84,
        "time": 13894,
        "intelligentContent": 122.48,
        "number_operators": 245,
        "number_operands": 581,
        "number_operators_unique": 14,
        "number_operands_unique": 90,
        "cloc": 119,
        "loc": 557,
        "lloc": 438,
        "mi": 37.56,
        "mIwoC": 4.74,
        "commentWeight": 32.82,
        "kanDefect": 5.63,
        "relativeStructuralComplexity": 289,
        "relativeDataComplexity": 1.78,
        "relativeSystemComplexity": 290.78,
        "totalStructuralComplexity": 4624,
        "totalDataComplexity": 28.44,
        "totalSystemComplexity": 4652.44,
        "package": "\\",
        "pageRank": 0.03,
        "afferentCoupling": 20,
        "efferentCoupling": 3,
        "instability": 0.13,
        "violations": {}
    },
    {
        "name": "ConsultationModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getConsultationById",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getConsultationsByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getConsultations",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getConsultationByAppointment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStatusClass",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "truncateText",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteConsultation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createConsultation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateConsultation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCountByStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateField",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateVitalSigns",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 13,
        "nbMethods": 13,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 13,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 72,
        "ccn": 60,
        "ccnMethodMax": 11,
        "externals": [
            "BaseModel",
            "Exception",
            "Exception",
            "Exception",
            "Exception"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 2,
        "length": 714,
        "vocabulary": 153,
        "volume": 5181.77,
        "difficulty": 23.21,
        "effort": 120291.2,
        "level": 0.04,
        "bugs": 1.73,
        "time": 6683,
        "intelligentContent": 223.21,
        "number_operators": 214,
        "number_operands": 500,
        "number_operators_unique": 13,
        "number_operands_unique": 140,
        "cloc": 121,
        "loc": 435,
        "lloc": 314,
        "mi": 47.91,
        "mIwoC": 11.45,
        "commentWeight": 36.46,
        "kanDefect": 3.14,
        "relativeStructuralComplexity": 169,
        "relativeDataComplexity": 2.66,
        "relativeSystemComplexity": 171.66,
        "totalStructuralComplexity": 2197,
        "totalDataComplexity": 34.64,
        "totalSystemComplexity": 2231.64,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 3,
        "efferentCoupling": 2,
        "instability": 0.4,
        "violations": {}
    },
    {
        "name": "DepartmentModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getAllDepartmentsWithStaffCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByName",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getActiveDepartments",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStaffInDepartment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createDepartment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateDepartment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "hasStaff",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDepartmentStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 8,
        "nbMethods": 8,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 32,
        "ccn": 25,
        "ccnMethodMax": 7,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 272,
        "vocabulary": 44,
        "volume": 1484.97,
        "difficulty": 21.44,
        "effort": 31844.26,
        "level": 0.05,
        "bugs": 0.49,
        "time": 1769,
        "intelligentContent": 69.25,
        "number_operators": 79,
        "number_operands": 193,
        "number_operators_unique": 8,
        "number_operands_unique": 36,
        "cloc": 47,
        "loc": 193,
        "lloc": 146,
        "mi": 61.82,
        "mIwoC": 27.22,
        "commentWeight": 34.61,
        "kanDefect": 0.78,
        "relativeStructuralComplexity": 81,
        "relativeDataComplexity": 1.78,
        "relativeSystemComplexity": 82.78,
        "totalStructuralComplexity": 648,
        "totalDataComplexity": 14.2,
        "totalSystemComplexity": 662.2,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 1,
        "efferentCoupling": 3,
        "instability": 0.75,
        "violations": {}
    },
    {
        "name": "DoctorModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getAllDoctorsWithDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorsByDepartment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorsBySpecialization",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "searchDoctors",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 8,
        "nbMethods": 8,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 41,
        "ccn": 34,
        "ccnMethodMax": 10,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 357,
        "vocabulary": 54,
        "volume": 2054.49,
        "difficulty": 25.4,
        "effort": 52184.17,
        "level": 0.04,
        "bugs": 0.68,
        "time": 2899,
        "intelligentContent": 80.89,
        "number_operators": 103,
        "number_operands": 254,
        "number_operators_unique": 9,
        "number_operands_unique": 45,
        "cloc": 51,
        "loc": 224,
        "lloc": 173,
        "mi": 57.1,
        "mIwoC": 23.41,
        "commentWeight": 33.69,
        "kanDefect": 1.06,
        "relativeStructuralComplexity": 81,
        "relativeDataComplexity": 1.84,
        "relativeSystemComplexity": 82.84,
        "totalStructuralComplexity": 648,
        "totalDataComplexity": 14.7,
        "totalSystemComplexity": 662.7,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 1,
        "efferentCoupling": 3,
        "instability": 0.75,
        "violations": {}
    },
    {
        "name": "DoctorScheduleModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getDoctorSchedule",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorSchedules",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "saveDoctorSchedule",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteDoctorSchedule",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAvailableTimeSlots",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllDoctorsWithSchedules",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDayNames",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 7,
        "nbMethods": 7,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 7,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 21,
        "ccn": 15,
        "ccnMethodMax": 6,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "AppointmentModel",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 3,
        "length": 215,
        "vocabulary": 63,
        "volume": 1285.12,
        "difficulty": 12.92,
        "effort": 16599.4,
        "level": 0.08,
        "bugs": 0.43,
        "time": 922,
        "intelligentContent": 99.49,
        "number_operators": 60,
        "number_operands": 155,
        "number_operators_unique": 9,
        "number_operands_unique": 54,
        "cloc": 49,
        "loc": 168,
        "lloc": 119,
        "mi": 68.06,
        "mIwoC": 30.94,
        "commentWeight": 37.12,
        "kanDefect": 0.8,
        "relativeStructuralComplexity": 169,
        "relativeDataComplexity": 1.14,
        "relativeSystemComplexity": 170.14,
        "totalStructuralComplexity": 1183,
        "totalDataComplexity": 8,
        "totalSystemComplexity": 1191,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 4,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "FollowUpModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getFollowUpWithDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFollowUpsByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFollowUpsByDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFollowUpsByConsultation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUpcomingFollowUps",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createFollowUp",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateFollowUp",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFollowUpsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countFollowUpsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getFollowUpStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStatusClass",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 12,
        "nbMethods": 12,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 12,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 75,
        "ccn": 64,
        "ccnMethodMax": 12,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 2,
        "length": 767,
        "vocabulary": 105,
        "volume": 5149.83,
        "difficulty": 35.23,
        "effort": 181406.78,
        "level": 0.03,
        "bugs": 1.72,
        "time": 10078,
        "intelligentContent": 146.19,
        "number_operators": 221,
        "number_operands": 546,
        "number_operators_unique": 12,
        "number_operands_unique": 93,
        "cloc": 79,
        "loc": 430,
        "lloc": 351,
        "mi": 40.69,
        "mIwoC": 9.88,
        "commentWeight": 30.81,
        "kanDefect": 2.55,
        "relativeStructuralComplexity": 121,
        "relativeDataComplexity": 2.38,
        "relativeSystemComplexity": 123.38,
        "totalStructuralComplexity": 1452,
        "totalDataComplexity": 28.58,
        "totalSystemComplexity": 1480.58,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 1,
        "efferentCoupling": 3,
        "instability": 0.75,
        "violations": {}
    },
    {
        "name": "InvoiceModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getInvoiceWithItems",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInvoiceItems",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInvoicesByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInvoicesByDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createInvoice",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addInvoiceItem",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generateInvoiceNumber",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getInvoiceStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 9,
        "nbMethods": 9,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 48,
        "ccn": 40,
        "ccnMethodMax": 14,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 495,
        "vocabulary": 97,
        "volume": 3266.96,
        "difficulty": 25.41,
        "effort": 83019.14,
        "level": 0.04,
        "bugs": 1.09,
        "time": 4612,
        "intelligentContent": 128.56,
        "number_operators": 135,
        "number_operands": 360,
        "number_operators_unique": 12,
        "number_operands_unique": 85,
        "cloc": 61,
        "loc": 286,
        "lloc": 225,
        "mi": 51.5,
        "mIwoC": 18.7,
        "commentWeight": 32.8,
        "kanDefect": 1.51,
        "relativeStructuralComplexity": 196,
        "relativeDataComplexity": 1.22,
        "relativeSystemComplexity": 197.22,
        "totalStructuralComplexity": 1764,
        "totalDataComplexity": 11,
        "totalSystemComplexity": 1775,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 3,
        "instability": 0.6,
        "violations": {}
    },
    {
        "name": "LabAttachmentModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "createAttachment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAttachmentsBySampleId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAttachmentById",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateComment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteAttachment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 5,
        "nbMethods": 5,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 5,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 23,
        "ccn": 19,
        "ccnMethodMax": 8,
        "externals": [
            "BaseModel",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 218,
        "vocabulary": 49,
        "volume": 1224.01,
        "difficulty": 13.08,
        "effort": 16014.09,
        "level": 0.08,
        "bugs": 0.41,
        "time": 890,
        "intelligentContent": 93.55,
        "number_operators": 61,
        "number_operands": 157,
        "number_operators_unique": 7,
        "number_operands_unique": 42,
        "cloc": 31,
        "loc": 147,
        "lloc": 116,
        "mi": 63.44,
        "mIwoC": 30.79,
        "commentWeight": 32.65,
        "kanDefect": 0.94,
        "relativeStructuralComplexity": 49,
        "relativeDataComplexity": 1.4,
        "relativeSystemComplexity": 50.4,
        "totalStructuralComplexity": 245,
        "totalDataComplexity": 7,
        "totalSystemComplexity": 252,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "LabParameterModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getParameterWithDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getParametersByTestType",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getActiveParameters",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getParametersFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countParametersFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createParameter",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateParameter",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "toggleActiveStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteParameter",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getParameterStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getParametersWithResults",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 11,
        "nbMethods": 11,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 11,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 63,
        "ccn": 53,
        "ccnMethodMax": 11,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 648,
        "vocabulary": 78,
        "volume": 4072.94,
        "difficulty": 38.01,
        "effort": 154802.14,
        "level": 0.03,
        "bugs": 1.36,
        "time": 8600,
        "intelligentContent": 107.16,
        "number_operators": 185,
        "number_operands": 463,
        "number_operators_unique": 11,
        "number_operands_unique": 67,
        "cloc": 71,
        "loc": 360,
        "lloc": 289,
        "mi": 45.66,
        "mIwoC": 13.91,
        "commentWeight": 31.75,
        "kanDefect": 1.76,
        "relativeStructuralComplexity": 121,
        "relativeDataComplexity": 1.95,
        "relativeSystemComplexity": 122.95,
        "totalStructuralComplexity": 1331,
        "totalDataComplexity": 21.42,
        "totalSystemComplexity": 1352.42,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "LabRequestModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getRequestWithDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRequestsByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRequestsByConsultation",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPendingRequests",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRequestsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countRequestsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createRequest",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateRequest",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "assignRequest",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markSampleCollected",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRequestStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getStatusClass",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPriorityClass",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 14,
        "nbMethods": 14,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 14,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 90,
        "ccn": 77,
        "ccnMethodMax": 13,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 3,
        "length": 851,
        "vocabulary": 116,
        "volume": 5836.14,
        "difficulty": 38.12,
        "effort": 222453.33,
        "level": 0.03,
        "bugs": 1.95,
        "time": 12359,
        "intelligentContent": 153.11,
        "number_operators": 247,
        "number_operands": 604,
        "number_operators_unique": 13,
        "number_operands_unique": 103,
        "cloc": 93,
        "loc": 476,
        "lloc": 383,
        "mi": 38.55,
        "mIwoC": 6.92,
        "commentWeight": 31.62,
        "kanDefect": 2.91,
        "relativeStructuralComplexity": 121,
        "relativeDataComplexity": 2.89,
        "relativeSystemComplexity": 123.89,
        "totalStructuralComplexity": 1694,
        "totalDataComplexity": 40.42,
        "totalSystemComplexity": 1734.42,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 0,
        "efferentCoupling": 3,
        "instability": 1,
        "violations": {}
    },
    {
        "name": "MedicalHistoryModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getPatientMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicalHistoryByType",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getOngoingConditions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicalHistorySummary",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getHistoryTypes",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 8,
        "nbMethods": 8,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 8,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 25,
        "ccn": 18,
        "ccnMethodMax": 7,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 5,
        "length": 227,
        "vocabulary": 64,
        "volume": 1362,
        "difficulty": 8.74,
        "effort": 11905.76,
        "level": 0.11,
        "bugs": 0.45,
        "time": 661,
        "intelligentContent": 155.81,
        "number_operators": 58,
        "number_operands": 169,
        "number_operators_unique": 6,
        "number_operands_unique": 58,
        "cloc": 53,
        "loc": 181,
        "lloc": 128,
        "mi": 66.84,
        "mIwoC": 29.67,
        "commentWeight": 37.18,
        "kanDefect": 1.08,
        "relativeStructuralComplexity": 144,
        "relativeDataComplexity": 1.17,
        "relativeSystemComplexity": 145.17,
        "totalStructuralComplexity": 1152,
        "totalDataComplexity": 9.38,
        "totalSystemComplexity": 1161.38,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 3,
        "instability": 0.6,
        "violations": {}
    },
    {
        "name": "MedicationModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllMedications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicationById",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCommonMedications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createMedication",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateMedication",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteMedication",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "isMedicationInUse",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCategories",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getBatches",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addBatch",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateBatch",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteBatch",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStockLevel",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "decrementStock",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getLowStockMedications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getExpiringMedications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getTotalMedications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicationCategories",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicationForms",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicationUnits",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getLowStockCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getOutOfStockCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateMedicationStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicationStock",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 25,
        "nbMethods": 25,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 25,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 91,
        "ccn": 67,
        "ccnMethodMax": 7,
        "externals": [
            "BaseModel",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 3,
        "length": 994,
        "vocabulary": 142,
        "volume": 7106.85,
        "difficulty": 39.76,
        "effort": 282552.76,
        "level": 0.03,
        "bugs": 2.37,
        "time": 15697,
        "intelligentContent": 178.75,
        "number_operators": 267,
        "number_operands": 727,
        "number_operators_unique": 14,
        "number_operands_unique": 128,
        "cloc": 175,
        "loc": 660,
        "lloc": 485,
        "mi": 41.22,
        "mIwoC": 5.43,
        "commentWeight": 35.79,
        "kanDefect": 3.76,
        "relativeStructuralComplexity": 289,
        "relativeDataComplexity": 2.96,
        "relativeSystemComplexity": 291.96,
        "totalStructuralComplexity": 7225,
        "totalDataComplexity": 73.94,
        "totalSystemComplexity": 7298.94,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 3,
        "instability": 0.6,
        "violations": {}
    },
    {
        "name": "MessageModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getInboxMessages",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getSentMessages",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getArchivedMessages",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMessageWithDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "archiveMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUnreadCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendMessage",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "searchMessages",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMessageStats",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 11,
        "nbMethods": 11,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 11,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 33,
        "ccn": 23,
        "ccnMethodMax": 5,
        "externals": [
            "BaseModel",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 2,
        "length": 493,
        "vocabulary": 77,
        "volume": 3089.53,
        "difficulty": 24.42,
        "effort": 75443.49,
        "level": 0.04,
        "bugs": 1.03,
        "time": 4191,
        "intelligentContent": 126.52,
        "number_operators": 124,
        "number_operands": 369,
        "number_operators_unique": 9,
        "number_operands_unique": 68,
        "cloc": 88,
        "loc": 317,
        "lloc": 229,
        "mi": 57.42,
        "mIwoC": 20.99,
        "commentWeight": 36.43,
        "kanDefect": 1.57,
        "relativeStructuralComplexity": 100,
        "relativeDataComplexity": 2.39,
        "relativeSystemComplexity": 102.39,
        "totalStructuralComplexity": 1100,
        "totalDataComplexity": 26.27,
        "totalSystemComplexity": 1126.27,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 3,
        "efferentCoupling": 2,
        "instability": 0.4,
        "violations": {}
    },
    {
        "name": "NotificationModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "create",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createAppointmentNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByGuestEmail",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAllAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUnreadCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteOldNotifications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPriorityByType",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getNotificationTemplate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserNotifications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserNotificationCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "timeAgo",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 14,
        "nbMethods": 14,
        "nbMethodsPrivate": 2,
        "nbMethodsPublic": 12,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 71,
        "ccn": 58,
        "ccnMethodMax": 11,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 5,
        "length": 767,
        "vocabulary": 148,
        "volume": 5529.65,
        "difficulty": 29.57,
        "effort": 163496.09,
        "level": 0.03,
        "bugs": 1.84,
        "time": 9083,
        "intelligentContent": 187.02,
        "number_operators": 201,
        "number_operands": 566,
        "number_operators_unique": 14,
        "number_operands_unique": 134,
        "cloc": 113,
        "loc": 399,
        "lloc": 286,
        "mi": 49.12,
        "mIwoC": 12.41,
        "commentWeight": 36.71,
        "kanDefect": 2.43,
        "relativeStructuralComplexity": 169,
        "relativeDataComplexity": 2.21,
        "relativeSystemComplexity": 171.21,
        "totalStructuralComplexity": 2366,
        "totalDataComplexity": 31,
        "totalSystemComplexity": 2397,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 3,
        "instability": 0.6,
        "violations": {}
    },
    {
        "name": "PatientModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getById",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByPatientNumber",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getWithUserData",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllPatientsWithUserData",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "searchPatients",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "addMedicalHistory",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updatePatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generatePatientNumber",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllPatients",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientIdByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRecentPatientsByDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientCountByDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 15,
        "nbMethods": 15,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 14,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 67,
        "ccn": 53,
        "ccnMethodMax": 11,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "UserModel",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "UserModel",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 635,
        "vocabulary": 97,
        "volume": 4190.94,
        "difficulty": 32.26,
        "effort": 135194.94,
        "level": 0.03,
        "bugs": 1.4,
        "time": 7511,
        "intelligentContent": 129.92,
        "number_operators": 178,
        "number_operands": 457,
        "number_operators_unique": 12,
        "number_operands_unique": 85,
        "cloc": 121,
        "loc": 461,
        "lloc": 340,
        "mi": 47.93,
        "mIwoC": 12.29,
        "commentWeight": 35.65,
        "kanDefect": 2.87,
        "relativeStructuralComplexity": 361,
        "relativeDataComplexity": 1.47,
        "relativeSystemComplexity": 362.47,
        "totalStructuralComplexity": 5415,
        "totalDataComplexity": 22,
        "totalSystemComplexity": 5437,
        "package": "\\",
        "pageRank": 0.02,
        "afferentCoupling": 12,
        "efferentCoupling": 4,
        "instability": 0.25,
        "violations": {}
    },
    {
        "name": "PaymentModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getPaymentWithDetails",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPaymentsByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPaymentsByInvoice",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createPayment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updatePaymentStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateInvoicePaymentStatus",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPaymentStatistics",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPaymentsByMethod",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPaymentsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countPaymentsFiltered",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updatePayment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deletePayment",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 12,
        "nbMethods": 12,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 11,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 76,
        "ccn": 65,
        "ccnMethodMax": 13,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 798,
        "vocabulary": 106,
        "volume": 5368.88,
        "difficulty": 43.67,
        "effort": 234480.02,
        "level": 0.02,
        "bugs": 1.79,
        "time": 13027,
        "intelligentContent": 122.93,
        "number_operators": 224,
        "number_operands": 574,
        "number_operators_unique": 14,
        "number_operands_unique": 92,
        "cloc": 82,
        "loc": 432,
        "lloc": 350,
        "mi": 40.89,
        "mIwoC": 9.64,
        "commentWeight": 31.24,
        "kanDefect": 2.19,
        "relativeStructuralComplexity": 144,
        "relativeDataComplexity": 1.96,
        "relativeSystemComplexity": 145.96,
        "totalStructuralComplexity": 1728,
        "totalDataComplexity": 23.54,
        "totalSystemComplexity": 1751.54,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 1,
        "efferentCoupling": 3,
        "instability": 0.75,
        "violations": {}
    },
    {
        "name": "PrescriptionModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createPrescription",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPrescriptionWithItems",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllPrescriptions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPendingPrescriptions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCompletedPrescriptions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPatientPrescriptions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPrescriptionItems",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorPrescriptions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "dispensePrescription",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cancelPrescription",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "generatePrescriptionNumber",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPrescriptionsByDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countPrescriptionsByDoctor",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPrescriptionsByStatus",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "countPrescriptions",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 17,
        "nbMethods": 17,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 16,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 71,
        "ccn": 55,
        "ccnMethodMax": 11,
        "externals": [
            "BaseModel",
            "Exception",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 830,
        "vocabulary": 130,
        "volume": 5828.57,
        "difficulty": 35.24,
        "effort": 205406.68,
        "level": 0.03,
        "bugs": 1.94,
        "time": 11411,
        "intelligentContent": 165.39,
        "number_operators": 246,
        "number_operands": 584,
        "number_operators_unique": 14,
        "number_operands_unique": 116,
        "cloc": 142,
        "loc": 513,
        "lloc": 371,
        "mi": 46.58,
        "mIwoC": 10.19,
        "commentWeight": 36.39,
        "kanDefect": 3.95,
        "relativeStructuralComplexity": 529,
        "relativeDataComplexity": 1.37,
        "relativeSystemComplexity": 530.37,
        "totalStructuralComplexity": 8993,
        "totalDataComplexity": 23.21,
        "totalSystemComplexity": 9016.21,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 3,
        "efferentCoupling": 3,
        "instability": 0.5,
        "violations": {}
    },
    {
        "name": "StaffModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getStaffIdByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 1,
        "nbMethods": 1,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 1,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 4,
        "ccn": 4,
        "ccnMethodMax": 4,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 1,
        "length": 38,
        "vocabulary": 18,
        "volume": 158.46,
        "difficulty": 5.58,
        "effort": 883.7,
        "level": 0.18,
        "bugs": 0.05,
        "time": 49,
        "intelligentContent": 28.41,
        "number_operators": 9,
        "number_operands": 29,
        "number_operators_unique": 5,
        "number_operands_unique": 13,
        "cloc": 9,
        "loc": 34,
        "lloc": 25,
        "mi": 89.33,
        "mIwoC": 53.56,
        "commentWeight": 35.77,
        "kanDefect": 0.22,
        "relativeStructuralComplexity": 49,
        "relativeDataComplexity": 0.38,
        "relativeSystemComplexity": 49.38,
        "totalStructuralComplexity": 49,
        "totalDataComplexity": 0.38,
        "totalSystemComplexity": 49.38,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 2,
        "efferentCoupling": 3,
        "instability": 0.6,
        "violations": {}
    },
    {
        "name": "UserModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "getByUsername",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getByEmail",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getWithRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "authenticate",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateLastLogin",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createRememberToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getActiveUserCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "checkRememberToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteRememberToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createUser",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateProfile",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "changePassword",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllUsers",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getRecentUsers",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserById",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllRoles",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "emailExists",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "usernameExists",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateUser",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteUser",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctorIdByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getDoctors",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllDoctors",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUsersByRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAllActiveUsers",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getCountByRole",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "storePasswordResetToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getPasswordResetToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deletePasswordResetToken",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deletePasswordResetTokenByUserId",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cleanupExpiredResetTokens",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 32,
        "nbMethods": 32,
        "nbMethodsPrivate": 1,
        "nbMethodsPublic": 31,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 119,
        "ccn": 88,
        "ccnMethodMax": 18,
        "externals": [
            "BaseModel",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler",
            "Exception",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 3,
        "length": 1088,
        "vocabulary": 95,
        "volume": 7148,
        "difficulty": 50.55,
        "effort": 361314.53,
        "level": 0.02,
        "bugs": 2.38,
        "time": 20073,
        "intelligentContent": 141.41,
        "number_operators": 316,
        "number_operands": 772,
        "number_operators_unique": 11,
        "number_operands_unique": 84,
        "cloc": 217,
        "loc": 839,
        "lloc": 622,
        "mi": 35.68,
        "mIwoC": 0.23,
        "commentWeight": 35.44,
        "kanDefect": 5.28,
        "relativeStructuralComplexity": 400,
        "relativeDataComplexity": 3.15,
        "relativeSystemComplexity": 403.15,
        "totalStructuralComplexity": 12800,
        "totalDataComplexity": 100.76,
        "totalSystemComplexity": 12900.76,
        "package": "\\",
        "pageRank": 0.03,
        "afferentCoupling": 16,
        "efferentCoupling": 3,
        "instability": 0.16,
        "violations": {}
    },
    {
        "name": "VitalSignModel",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "createVitalSign",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getVitalSignById",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getVitalSignsByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getLatestVitalSignByPatient",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "updateVitalSign",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "deleteVitalSign",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 7,
        "nbMethods": 7,
        "nbMethodsPrivate": 0,
        "nbMethodsPublic": 7,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 19,
        "ccn": 13,
        "ccnMethodMax": 4,
        "externals": [
            "BaseModel",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler",
            "ErrorHandler"
        ],
        "parents": [
            "BaseModel"
        ],
        "implements": [],
        "lcom": 4,
        "length": 137,
        "vocabulary": 32,
        "volume": 685,
        "difficulty": 15.83,
        "effort": 10845.83,
        "level": 0.06,
        "bugs": 0.23,
        "time": 603,
        "intelligentContent": 43.26,
        "number_operators": 42,
        "number_operands": 95,
        "number_operators_unique": 8,
        "number_operands_unique": 24,
        "cloc": 40,
        "loc": 134,
        "lloc": 94,
        "mi": 72.8,
        "mIwoC": 35.35,
        "commentWeight": 37.45,
        "kanDefect": 0.66,
        "relativeStructuralComplexity": 121,
        "relativeDataComplexity": 1.18,
        "relativeSystemComplexity": 122.18,
        "totalStructuralComplexity": 847,
        "totalDataComplexity": 8.25,
        "totalSystemComplexity": 855.25,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 3,
        "efferentCoupling": 2,
        "instability": 0.4,
        "violations": {}
    },
    {
        "name": "NotificationService",
        "interface": false,
        "abstract": false,
        "final": false,
        "methods": [
            {
                "name": "__construct",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendAppointmentNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getAppointmentNotificationData",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getGuestPatientInfo",
                "role": null,
                "public": false,
                "private": true,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUserNotifications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getUnreadCount",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "markAllAsRead",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendAppointmentCreatedNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendAppointmentUpdatedNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendAppointmentCancelledNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendAppointmentCompletedNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendAppointmentReminderNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "cleanupOldNotifications",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "sendMessageNotification",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            },
            {
                "name": "getNotificationStats",
                "role": null,
                "public": true,
                "private": false,
                "_type": "Hal\\Metric\\FunctionMetric"
            }
        ],
        "nbMethodsIncludingGettersSetters": 16,
        "nbMethods": 16,
        "nbMethodsPrivate": 2,
        "nbMethodsPublic": 14,
        "nbMethodsGetter": 0,
        "nbMethodsSetters": 0,
        "wmc": 42,
        "ccn": 27,
        "ccnMethodMax": 12,
        "externals": [
            "NotificationModel",
            "AppointmentModel",
            "UserModel",
            "PatientModel",
            "StaffModel",
            "MessageModel",
            "Exception",
            "Exception"
        ],
        "parents": [],
        "implements": [],
        "lcom": 2,
        "length": 367,
        "vocabulary": 95,
        "volume": 2411.14,
        "difficulty": 12.69,
        "effort": 30596.5,
        "level": 0.08,
        "bugs": 0.8,
        "time": 1700,
        "intelligentContent": 190.01,
        "number_operators": 91,
        "number_operands": 276,
        "number_operators_unique": 8,
        "number_operands_unique": 87,
        "cloc": 118,
        "loc": 291,
        "lloc": 173,
        "mi": 65.57,
        "mIwoC": 23.87,
        "commentWeight": 41.71,
        "kanDefect": 0.99,
        "relativeStructuralComplexity": 225,
        "relativeDataComplexity": 1.46,
        "relativeSystemComplexity": 226.46,
        "totalStructuralComplexity": 3600,
        "totalDataComplexity": 23.38,
        "totalSystemComplexity": 3623.38,
        "package": "\\",
        "pageRank": 0.01,
        "afferentCoupling": 5,
        "efferentCoupling": 7,
        "instability": 0.58,
        "violations": {}
    }
]