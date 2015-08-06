The GET method should always take 'height' and 'width' parameters. Photos are currently stored at their uploaded
resolution. Obviously, such high resolution images aren't necessary for mobile and/or web display. In the future,
a CDN (content distribution network) will be used to cache these resized images so the calculation is only used once.

An example request would be 'api/v1/media/lkjsdflkjsflsjf.jpg?width=100&height=50'.