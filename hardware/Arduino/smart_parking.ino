#include <Wire.h>
#include <PCF8574.h>
#include <Servo.h>

// ── PCF8574 ───────────────────────────────────────
PCF8574 pcf(0x20);

// ── Servos ────────────────────────────────────────
Servo entryServo;
Servo exitServo;

// ── Direct Arduino Pins ───────────────────────────
#define IR1_PIN     2    // Button - Entry gate detection
#define TRIG1       3
#define ECHO1       4
#define TRIG2       5
#define ECHO2       6
#define TRIG3       7
#define ECHO3       8
#define ENTRY_SERVO 9
#define TCRT1_PIN   10
#define TCRT2_PIN   11
#define TCRT3_PIN   12
#define EXIT_SERVO  13
#define TRIG4       A0
#define ECHO4       A1
#define IR2_PIN     A2   // Level entrance → triggers slot 1 & 2 check
#define IR3_PIN     A3   // Middle of level → triggers slot 3 & 4 check

// ── PCF8574 Pins ──────────────────────────────────
#define P_LED1    0
#define P_LED2    1
#define P_LED3    2
#define P_LED4    3
#define P_CAM_LED 4

// ── Threshold ─────────────────────────────────────
#define OCCUPIED_THRESHOLD 100  // cm — handles pot jumps in Proteus

// ── State Machine ─────────────────────────────────
int state = 0;
unsigned long stateTimer = 0;

// ── Assigned slot tracking ────────────────────────
int assignedSlot = 0;   // 1–4
int assignedLed  = 0;   // PCF pin for that slot's LED

// ── Double Parking Tracking ───────────────────────
bool tcrt1Last = HIGH;
bool tcrt2Last = HIGH;
bool tcrt3Last = HIGH;


// ════════════════════════════════════════════════════
//                    HELPER FUNCTIONS
// ════════════════════════════════════════════════════

long getDistance(int trig, int echo) {
  digitalWrite(trig, LOW);
  delayMicroseconds(2);
  digitalWrite(trig, HIGH);
  delayMicroseconds(10);
  digitalWrite(trig, LOW);
  long duration = pulseIn(echo, HIGH, 30000);
  if (duration == 0) return 999;
  return duration * 0.034 / 2;
}

void openEntryGate()  { entryServo.write(90); Serial.println("[GATE] Entry gate OPEN."); }
void closeEntryGate() { entryServo.write(0);  Serial.println("[GATE] Entry gate CLOSED."); }
void openExitGate()   { exitServo.write(90);  Serial.println("[GATE] Exit gate OPEN."); }
void closeExitGate()  { exitServo.write(0);   Serial.println("[GATE] Exit gate CLOSED."); }

void cameraOn()  { pcf.write(P_CAM_LED, HIGH); Serial.println("[CAM]  Camera LED ON — scanning vehicle."); }
void cameraOff() { pcf.write(P_CAM_LED, LOW);  Serial.println("[CAM]  Camera LED OFF."); }

void slotLedOn(int ledPin) {
  pcf.write(ledPin, HIGH);
  Serial.print("[LED]  Slot ");
  Serial.print(assignedSlot);
  Serial.println(" LED ON — guiding vehicle.");
}

void slotLedOff(int ledPin) {
  pcf.write(ledPin, LOW);
  Serial.print("[LED]  Slot ");
  Serial.print(assignedSlot);
  Serial.println(" LED OFF.");
}

void resetSystem() {
  closeExitGate();
  closeEntryGate();
  cameraOff();
  for (int i = 0; i <= 3; i++) pcf.write(i, LOW); // turn off all slot LEDs
  assignedSlot = 0;
  assignedLed  = 0;
  state = 0;
  Serial.println("==============================");
  Serial.println("[SYS]  Sequence complete.     ");
  Serial.println("[SYS]  System reset — ready.  ");
  Serial.println("==============================");
}

void checkDoubleParking() {
  bool t1 = digitalRead(TCRT1_PIN);
  bool t2 = digitalRead(TCRT2_PIN);
  bool t3 = digitalRead(TCRT3_PIN);

  if (t1 == LOW && tcrt1Last == HIGH) Serial.println("[TCRT1] !! Double parking at Zone 1!");
  if (t2 == LOW && tcrt2Last == HIGH) Serial.println("[TCRT2] !! Double parking at Zone 2!");
  if (t3 == LOW && tcrt3Last == HIGH) Serial.println("[TCRT3] !! Double parking at Zone 3!");

  tcrt1Last = t1;
  tcrt2Last = t2;
  tcrt3Last = t3;
}

// ── Find first free slot in range, returns slot# or 0 if all full ──
// trig/echo arrays for slots 1-4
int trigPins[] = {TRIG1, TRIG2, TRIG3, TRIG4};
int echoPins[] = {ECHO1, ECHO2, ECHO3, ECHO4};

int findFreeSlot(int from, int to) {
  for (int s = from; s <= to; s++) {
    long d = getDistance(trigPins[s-1], echoPins[s-1]);
    Serial.print("[U");
    Serial.print(s);
    Serial.print("]  Distance: ");
    if (d == 999) Serial.println("--- cm"); else { Serial.print(d); Serial.println(" cm"); }
    if (d == 999 || d >= OCCUPIED_THRESHOLD) {
      return s;  // slot is free
    }
  }
  return 0;  // all full in this range
}


// ════════════════════════════════════════════════════
//                    STATE FUNCTIONS
// ════════════════════════════════════════════════════

// STATE 0 — Wait for entry IR button
void state0_waitForEntry() {
  if (digitalRead(IR1_PIN) == LOW) {
    Serial.println("[IR1]  Vehicle detected at entry gate.");
    delay(300);
    cameraOn();
    delay(200);
    stateTimer = millis();
    state = 1;
  }
}

// STATE 1 — Open entry gate after 1s
void state1_openEntryGate() {
  if (millis() - stateTimer >= 1000) {
    openEntryGate();
    stateTimer = millis();
    state = 2;
  }
}

// STATE 2 — Wait for IR2 (level entrance) → check slots 1 & 2
void state2_levelEntrance() {
  if (digitalRead(IR2_PIN) == LOW) {
    closeEntryGate();
    Serial.println("[IR2]  Car entered parking level (entrance).");
    Serial.println("[SYS]  Checking slots 1 & 2...");
    delay(200);

    // Check slots 1 and 2
    int slot = findFreeSlot(1, 2);
    if (slot > 0) {
      assignedSlot = slot;
      assignedLed  = slot - 1;  // PCF pins P0=slot1, P1=slot2
      Serial.print("[SYS]  >> Slot ");
      Serial.print(assignedSlot);
      Serial.println(" assigned. LED ON.");
      slotLedOn(assignedLed);
      stateTimer = millis();
      state = 4;  // go wait for parking
    } else {
      Serial.println("[SYS]  Slots 1 & 2 full. Waiting for middle sensor...");
      stateTimer = millis();
      state = 3;  // go to middle checkpoint
    }
  }
}

// STATE 3 — Wait for IR3 (middle) → check slots 3 & 4
void state3_middleCheckpoint() {
  if (digitalRead(IR3_PIN) == LOW) {
    Serial.println("[IR3]  Car passed middle checkpoint.");
    Serial.println("[SYS]  Checking slots 3 & 4...");
    delay(200);

    int slot = findFreeSlot(3, 4);
    if (slot > 0) {
      assignedSlot = slot;
      assignedLed  = slot - 1;  // PCF pins P2=slot3, P3=slot4
      Serial.print("[SYS]  >> Slot ");
      Serial.print(assignedSlot);
      Serial.println(" assigned. LED ON.");
      slotLedOn(assignedLed);
      stateTimer = millis();
      state = 4;
    } else {
      Serial.println("[SYS]  All slots FULL. Gate stays closed.");
      stateTimer = millis();
      state = 0;  // reset, gate stays closed
    }
  }
}

// STATE 4 — Wait for car to park in assigned slot
void state4_waitForParking() {
  if (millis() - stateTimer >= 1000) {
    long d = getDistance(trigPins[assignedSlot-1], echoPins[assignedSlot-1]);

    Serial.print("[U");
    Serial.print(assignedSlot);
    Serial.print("]  Distance: ");
    if (d == 999) Serial.println("--- cm");
    else { Serial.print(d); Serial.println(" cm"); }

    if (d != 999 && d < OCCUPIED_THRESHOLD) {
      slotLedOff(assignedLed);
      cameraOff();
      Serial.println("------------------------------");
      Serial.print("[U");
      Serial.print(assignedSlot);
      Serial.println("]  >> Slot OCCUPIED — vehicle parked.");
      Serial.println("[SYS]  Timer started. Billing active.");
      Serial.println("------------------------------");
      stateTimer = millis();
      state = 5;
    } else {
      Serial.println("[SYS]  Waiting for vehicle to reach slot...");
      stateTimer = millis();
    }
  }
}

// STATE 5 — Wait for car to leave
void state5_waitForDeparture() {
  if (millis() - stateTimer >= 2000) {
    long d = getDistance(trigPins[assignedSlot-1], echoPins[assignedSlot-1]);

    Serial.print("[U");
    Serial.print(assignedSlot);
    Serial.print("]  Distance: ");
    if (d == 999) Serial.println("--- cm");
    else { Serial.print(d); Serial.println(" cm"); }

    if (d == 999 || d >= OCCUPIED_THRESHOLD) {
      Serial.println("------------------------------");
      Serial.print("[U");
      Serial.print(assignedSlot);
      Serial.println("]  >> Slot EMPTY — vehicle departed.");
      Serial.println("[SYS]  Calculating cost...");
      Serial.println("------------------------------");
      stateTimer = millis();
      state = 6;
    } else {
      Serial.println("[SYS]  Vehicle still parked...");
      stateTimer = millis();
    }
  }
}

// STATE 6 — Payment confirmed → open exit gate
void state6_openExitGate() {
  if (millis() - stateTimer >= 1000) {
    Serial.println("[PAY]  Payment confirmed.");
    openExitGate();
    stateTimer = millis();
    state = 7;
  }
}

// STATE 7 — Close exit gate → reset
void state7_resetSystem() {
  if (millis() - stateTimer >= 2000) {
    resetSystem();
  }
}


// ════════════════════════════════════════════════════
//                    SETUP & LOOP
// ════════════════════════════════════════════════════

void setup() {
  Serial.begin(9600);
  Wire.begin();

  pcf.begin();
  for (int i = 0; i <= 4; i++) pcf.write(i, LOW);

  entryServo.attach(ENTRY_SERVO);
  exitServo.attach(EXIT_SERVO);
  entryServo.write(0);
  exitServo.write(0);

  pinMode(IR1_PIN,   INPUT_PULLUP);
  pinMode(IR2_PIN,   INPUT_PULLUP);
  pinMode(IR3_PIN,   INPUT_PULLUP);
  pinMode(TCRT1_PIN, INPUT_PULLUP);
  pinMode(TCRT2_PIN, INPUT_PULLUP);
  pinMode(TCRT3_PIN, INPUT_PULLUP);
  pinMode(TRIG1, OUTPUT); pinMode(ECHO1, INPUT);
  pinMode(TRIG2, OUTPUT); pinMode(ECHO2, INPUT);
  pinMode(TRIG3, OUTPUT); pinMode(ECHO3, INPUT);
  pinMode(TRIG4, OUTPUT); pinMode(ECHO4, INPUT);

  Serial.println("==============================");
  Serial.println("   Smart Parking System ON    ");
  Serial.println("==============================");

  stateTimer = millis();
}

void loop() {
  checkDoubleParking();

  switch (state) {
    case 0: state0_waitForEntry();     break;
    case 1: state1_openEntryGate();    break;
    case 2: state2_levelEntrance();    break;
    case 3: state3_middleCheckpoint(); break;
    case 4: state4_waitForParking();   break;
    case 5: state5_waitForDeparture(); break;
    case 6: state6_openExitGate();     break;
    case 7: state7_resetSystem();      break;
  }
}
