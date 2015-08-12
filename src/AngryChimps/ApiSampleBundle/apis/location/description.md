Note that walk-ins and emergencies are listed as services as well as individual flags. For the POST and PATCH
methods, you should only supply the services array (supplying the flags will result in an error).
GET requests will return both the list of services as well as the flags (which will always match
the services).