The staff GET endpoint functions as a normal REST endpoint when given an id in the form
/api/v1/staff/ljsdflksjflkjslkjsdlfkjsdf.

The api will also return an array of staff objects if called without
the {id} parameter when given either a location_id or company_id GET parameter. An optional 'count' parameter
can be passed to limit the number returned. Without the "count" parameter, all results will be returned.

Normally, this api will be called first with a location_id and count parameter for the initial page display.
If the user clicks the 'view all' link, the system should make the same API call without the 'count' parameter to
return all results.