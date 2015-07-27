Note that walk-ins and emergencies are listed as services as well as individual flags. For the POST, you will
only supply the services array. PATCH requests can supply either a full set of services or individual flags (but not 
both in one request). GET requests will return both the list of services as well as the flags (which will always match
the services).