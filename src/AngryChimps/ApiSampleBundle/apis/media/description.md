The media endpoint is a REST-ish style endpoint. Media can be posted by submitting the file in a form field
named "media". To view the media, simply request it by id for example:
/api/v1/media/2a3701c951e5390b7dacc26ec6ad4a9b1a0d1207.jpg.

Once you have uploaded media, you will need to update the associated member, location or staff object with the
id returned (xxxxxx.jpg).

There is no way to delete media or modify it, to modify an image, upload a new one and then issue a PATCH
request to the appropriate endpoint with updated photo/photos data.