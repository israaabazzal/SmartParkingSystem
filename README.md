# 🚗 Smart Parking System

An **Embedded Smart Parking System** integrating **Arduino**, **PHP**, **MySQL**, **ESP32-CAM**, and multiple sensors to automate parking management, vehicle detection, gate control, reservation, and payment processing through a real-time web application.

The system combines embedded hardware with a web-based management platform to provide an efficient parking solution capable of monitoring parking spaces, controlling vehicle access, managing reservations, and improving parking efficiency.

---

# 📖 Overview

The Smart Parking System was developed as an embedded systems project that integrates hardware and software into one intelligent parking solution.

The system detects vehicle movement using multiple sensors, automatically controls entry and exit gates through servo motors, monitors parking availability in real time, and provides users with a web application for parking reservation and payment management.

Administrators can monitor parking activity, manage reservations, view statistics, and oversee parking operations through an intuitive dashboard.

---

# ✨ Features

## 🚘 Vehicle Detection

- Automatic vehicle detection using IR sensors
- Parking slot occupancy monitoring
- Vehicle entry and exit tracking
- Real-time parking status updates

---

## 🚧 Automated Gate Control

- Automatic entry gate operation
- Automatic exit gate operation
- Servo motor controlled barriers
- Sensor-triggered gate movement

---

## 📷 Camera Monitoring

- ESP32-CAM integration
- Vehicle monitoring at parking entrance
- Live camera streaming support
- Parking surveillance

---

## 🅿 Parking Management

- Multi-level parking support
- Parking slot availability monitoring
- Dynamic parking occupancy updates
- Automatic slot assignment

---

## 📅 Reservation System

- Online parking reservation
- Reservation management
- Reservation validation
- Reservation history

---

## 💳 Payment Management

- Parking payment processing
- Parking session tracking
- Automatic parking fee calculation
- Payment history

---

## 👤 User Portal

- User registration
- Secure login
- Profile management
- Parking reservation dashboard
- Parking session history

---

## 👨‍💼 Administrator Dashboard

- User management
- Parking statistics
- Reservation management
- Payment monitoring
- Parking session management
- System analytics

---

## 📊 Reports & Statistics

- Parking occupancy statistics
- Active parking sessions
- Reservation reports
- Payment reports
- Parking utilization overview

---

# 🔌 Hardware Components

The embedded system integrates multiple hardware components:

- Arduino Uno
- ESP32-CAM
- IR Sensors
- Ultrasonic Sensors
- TCRT5000 Sensors
- Servo Motors
- LCD Display
- Buzzer
- LEDs
- Push Buttons

---

# 💻 Software Technologies

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- AJAX
- XAMPP
- Arduino IDE
- Proteus Professional

---

# ⚙ System Architecture

The system consists of three main components:

### Embedded Layer

- Arduino controls sensors and gate mechanisms.
- Reads parking occupancy.
- Controls entry and exit barriers.
- Communicates parking status.

### Database Layer

- MySQL stores:
  - Users
  - Reservations
  - Parking sessions
  - Payments
  - Parking slot information

### Web Application Layer

Provides interfaces for:

- Users
- Administrators

Features include:

- Login
- Reservation
- Payment
- Monitoring
- Reporting

---

# 🚀 System Workflow

1. User logs into the web application.
2. User reserves a parking space.
3. Vehicle approaches the entrance.
4. IR sensor detects the vehicle.
5. Arduino validates access.
6. Servo motor opens the entrance gate.
7. Vehicle enters the parking area.
8. Ultrasonic and IR sensors update parking occupancy.
9. ESP32-CAM monitors vehicle movement.
10. User exits the parking area.
11. Exit gate opens automatically.
12. Parking session is completed.
13. Payment information is stored.
14. Dashboard updates parking statistics in real time.

---

# 📂 Project Structure

```
SmartParkingSystem
│
├── api/
│   ├── Authentication APIs
│   ├── Reservation APIs
│   ├── Payment APIs
│   ├── Parking APIs
│   └── Statistics APIs
│
├── css/
│
├── js/
│
├── image/
│
├── database/
│   └── smart_parking.sql
│
├── admin.php
├── admin_login.php
├── index.php
├── user.php
│
├── README.md
└── .gitignore
```

---

# 🗄 Database

The MySQL database manages:

- Users
- Reservations
- Parking Slots
- Parking Sessions
- Payments
- Statistics

Import the SQL file located in:

```
database/smart_parking.sql
```

before running the application.

---

# ⚙ Installation

## 1. Clone the repository

```bash
git clone https://github.com/israaabazzal/SmartParkingSystem.git
```

---

## 2. Copy the project

Move the project folder into:

```
xampp/htdocs/
```

---

## 3. Import the database

Open **phpMyAdmin** or **MySQL Workbench**.

Import:

```
database/smart_parking.sql
```

---

## 4. Configure the database connection

Update the database credentials inside the PHP configuration file.

Example:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "smart_parking";
```

---

## 5. Start Apache & MySQL

Launch:

- Apache
- MySQL

using XAMPP Control Panel.

---

## 6. Open the application

Navigate to:

```
http://localhost/smart-parking
```

---

# 📸 Screenshots

<p align="center">
  <img src="screenshots/homePage.png" width="45%">
  <img src="screenshots/userDashboard.png" width="45%">
</p>

<p align="center">
  <img src="screenshots/adminDashboard.png" width="45%">
  <img src="screenshots/adminDashboard2.png" width="45%">
</p>

<p align="center">
  <img src="screenshots/slotAssignmentMap.png" width="45%">
  <img src="screenshots/Duration&Billing.png" width="45%">
</p>
---

# 🔄 Hardware Simulation

The embedded hardware can be simulated using **Proteus Professional**.

The simulation includes:

- Arduino Uno
- IR Sensors
- Ultrasonic Sensors
- Servo Motors
- ESP32-CAM integration
- Vehicle detection
- Automatic gate control

---

# 📈 Future Enhancements

Potential future improvements include:

- Mobile application integration
- QR Code parking access
- RFID authentication
- License Plate Recognition (LPR)
- Cloud database synchronization
- IoT-based remote monitoring
- Mobile payment gateways
- AI-powered parking prediction
- Email and SMS notifications

---

# 📌 Requirements

### Software

- PHP 8+
- MySQL
- XAMPP
- Arduino IDE
- Proteus Professional

### Hardware

- Arduino Uno
- ESP32-CAM
- IR Sensors
- Ultrasonic Sensors
- Servo Motors
- TCRT Sensors
- LCD Display

---

# 👨‍💻 Authors

**Israa Bazzal**

Bachelor of Computer & Communication Engineering

Developed as an Embedded Systems and Web Application project integrating Arduino, sensors, automation, and PHP/MySQL technologies.

---

# 📄 License

This project was developed for educational purposes.
