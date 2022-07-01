HelloImage
-------------

An example RESTfull symfony image upload service;       
A register-less image upload service;        

When visitting a website for the first time sets a unique AUTH cookie;     
When you make an api request requiring a cookie your       

## API:       
| Path | Methods | Function | JsonResp |       
| - | - | - | - |       
| /image ReqCookie: $auth | POST | Upload a new file, returns link to the image(actually better redirect), sets an $auth cookie | none |       
| /image/${id} ReqCookie: $auth | GET/DELETE | Resolves the extension/Removes the file | { 'ext': '{.png/.webp/.jpeg}, 'uid': number  }  |       
| /rating/${id} ReqCookie: $auth | GET/POST/DELETE | Views/Increments/Decrements the rating of an image | { 'rating': number } / none / none |       
| /list ReqCookie: $uid | GET | Lists all images made by you | { [ 'imgs': {'imgID': number, 'url': string }, 'imgs'(...) ] } |       
| /list/${uid} | GET | Lists all images made by user | { [ 'imgs': {'imgID': number, 'url': string }, 'imgs'(...) ] } |       

Image access:       
| Path | Methods | Function | JsonResp |       
| - | - | - | - |       
| /uploads/${id}.{png/webp/jpeg} | GET | Image path | none |       
| /uploads/${id} | GET | Webpage | none |  

## SQL Tables:       

user table:       
| Field type | Name | Notes |       
| - | - | - |       
| Key/Int(auto increment) | uid | |       
| String | $auth | Unique auth cookie |       

image table:       
| Field type | Name | Note |        
| - | - | - |       
| Key/Int(auto increment) | imgID | - |       
| Int | uid | - |       
| String | extension | - |   
| Int | voteCount | This should prevent a race condition on creation of new rating entry |

rating table:       
| Field type | Name | Note |        
| - | - | - |     
| Key/Int(auto increment) | voteId | - |     
| Int | imgID | - |       
| Int | uid | - |       