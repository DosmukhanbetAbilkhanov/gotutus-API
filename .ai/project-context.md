This is a mobile-first social application designed to help people find companions for casual, real-life hangouts and shared activities in their city. Built as a native mobile app using Flutter, the platform focuses on spontaneous, low-pressure social interactions such as grabbing a beer, having coffee, going to a bathhouse, taking a walk, or similar everyday activities.

All users are registered and associated with a specific city. Activity requests are visible only to users within the same city, ensuring local relevance and practical meetups. The app emphasizes flexibility, mutual agreement, and real-world connections rather than online socializing or dating

This Laravel 12 application serves as a RESTful API backend for a mobile social application built with Flutter. The backend is responsible for all core business logic, authentication, authorization, data persistence, and system rules.

The API enables users to create and join casual, real-life hangout requests within their city. Hangouts are activity-based (e.g., grabbing a beer, coffee, walking, bathhouse), date-driven, and visible only to users registered in the same city.

The backend manages:
	•	User authentication and profile data
	•	City-based access control
	•	Activity types and related places
	•	Hangout request lifecycle (creation, joining, approval, confirmation, closure)
	•	Messaging between matched participants
	•	Notifications and moderation tools

This application does not render any UI and does not use Blade or Livewire. All interactions occur through JSON-based API endpoints consumed by a Flutter mobile application. The backend acts as the single source of truth for application state and business rules.

How It Works

1. Registration & City Setup

Users sign up through the mobile app and select their city during onboarding. The city determines which activity requests are visible to the user and ensures all interactions remain local.

⸻

2. Create a Hangout Request

A registered user can create a hangout request directly from the mobile app by specifying:
	•	Activity type (e.g., grabbing a beer, coffee, walk, bathhouse)
	•	Date (mandatory)
	•	Time (optional)
	•	City (automatically based on the user’s profile)
	•	Specific location (optional)

When an activity type is selected, the app displays suggested places related to that activity within the user’s city.
For example:
	•	“Grabbing a beer” → pubs and bars
	•	“Coffee” → cafés
	•	“Bathhouse” → saunas and bathhouses

Selecting a specific place is optional. If no place is chosen, the request remains open-ended, allowing participants to decide on the exact venue together later.

⸻

3. Browse Local Requests

Users can browse open hangout requests created by other users in the same city through a mobile feed optimized for quick scanning and easy interaction. Requests can be filtered by activity type and date.

⸻

4. Join a Hangout

When a user finds an interesting request, they can send a join request with an optional message.
If the requester did not select a specific location, the joining user may also suggest a suitable place related to the chosen activity.

⸻

5. Approval & Mutual Confirmation

The request creator reviews incoming join requests and can approve or decline participants.
After approval, the joining user confirms participation. Once both sides confirm, the hangout request is closed and participant details are shared.

⸻

6. Coordination & Messaging

After mutual confirmation, in-app messaging becomes available, allowing participants to coordinate details such as the final location, meeting time, or other logistics.

⸻

7. Safety & Control

The mobile app includes safety features such as user blocking, reporting, and controlled sharing of personal information. Contact details and messaging are only available after mutual agreement.

⸻

Positioning
	•	Not a dating app
	•	Not a social network
	•	A mobile tool for turning free time into shared real-life experiences