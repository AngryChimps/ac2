Unfortunately, uploading an image requires either a multipart/form-data request or base64 encoding the image
before adding it to a regular JSON upload. Unfortunately, base64 encoding increases image size by about 33%.

We have chosen for this method to accept a multipart/form-data style request with the media in the 'media' form
tag.

This endpoint also accepts information as GET params to specify image cropping. All measurements are in pixels. The
following GET parameters are supported (and must be used all or none):
* top_x
* top_y
* bottom_x
* bottom_y