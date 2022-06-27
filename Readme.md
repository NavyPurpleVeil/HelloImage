HelloImage
-------------

An example symfony image upload service;
A register-less image upload service; 

When visitting a website for the first time sets a unique UID cookie;
When you make an api request requiring a cookie your

API:
| Path | Methods | Function |
| - | - | - |
| /image ReqCookie: $uid | POST | Upload a new file, returns link to the image(actually better redirect) |
| /image/${id} ReqCookie: $uid | GET/DELETE | Resolves the extension/Removes the file |
| /rating/${id} ReqCookie: $uid | GET/DELETE | Increments/Decrements the rating of an image |
| /list ReqCookie: $uid | GET | Lists all images made by you |

Image access:
| /uploads/${id}.{png/webp/jpeg} | GET | Gets an image |