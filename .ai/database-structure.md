Database

1. users 

Columns	
id
name
email (nullable)
phone
phone_verified_at
password
city_id
status
created_at
updated_at

1-2. user_photo 

Columns	
id
user_id
photo_url
is_approved
created_at
updated_at

2. cities

Columns
id
is_active
created_at
updated_at

city_translations

Columns
id
city_id
language_code (kz, ru, en)
name


3. activity_types

Columns
id
slug
bg_photo
icon
is_active
created_at
updated_at

activity_type_translations

Columns
id
activity_type_id
language_code
name


4. places

Columns
id
city_id
created_at
updated_at

place_translations

Columns
id
place_id
language_code
name
address (nullable)

5. activity_type_place 

Columns
id
activity_type_id
place_id

6. hangout_requests

Columns
id
user_id
city_id
activity_type_id
place_id (nullable)
date
time (nullable)
status
created_at
updated_at
notes (nullable)

7. join_requests

Columns	
id
hangout_request_id
user_id
place_id (nullable)
status
confirmed_at
created_at
updated_at
message (nullable)

8. conversations

Columns
id
hangout_request_id
created_at
updated_at


9. messages

Columns
id
conversation_id
user_id
message
created_at

10. reports

Columns
id
reporter_id
reported_user_id
hangout_request_id (nullable)
reason
created_at

11. blocked_users

Columns
id
user_id
blocked_user_id
created_at