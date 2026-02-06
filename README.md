Mobile Hangout Companion App

This project is a mobile-first social application that helps users find companions for casual, real-life hangouts and shared activities in their city.

The mobile application is built with Flutter, providing a fast and responsive native experience for both iOS and Android. The backend is implemented using Laravel 12, which serves as a RESTful API handling authentication, business logic, and data persistence.

Core Concept

Users can create hangout requests by selecting an activity type (such as grabbing a beer, coffee, walking, or visiting a bathhouse), choosing a date, and optionally specifying a time and location. Requests are visible only to users within the same city. Other users can join these requests, and participation is confirmed through mutual approval. Once confirmed, participant details are shared to allow direct coordination.

The platform emphasizes:
	•	Spontaneous, short-term social interactions
	•	City-based discovery
	•	Flexibility in choosing time and place
	•	Real-world connections over online engagement

Tech Stack
	•	Mobile App: Flutter (iOS & Android)
	•	Backend API: Laravel 12
	•	Database: MySQL / PostgreSQL
	•	Authentication: Token-based (e.g. Sanctum)
	•	Notifications: Firebase Cloud Messaging (FCM)

    Architecture

Flutter communicates with the Laravel backend exclusively through a versioned REST API. The backend enforces all business rules and serves as the single source of truth, while the mobile client focuses on presentation and user experience.