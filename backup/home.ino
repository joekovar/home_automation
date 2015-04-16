/*

	Web Server
	Circuits:
		Ethernet shield attached to pins 10, 11, 12, 13 | 50, 51, 52, 53
		SD Card on pin 4
*/

#define VERSION "1.0.2"

#include "SPI.h"
#include "avr/pgmspace.h"
#include "Time.h"
#include "TimeAlarms.h"
#include "Ethernet.h"
#include "WebServer.h"
#include "SD.h"
#include "dht.h"

// no-cost stream operator as described at 
// http://sundial.org/arduino/?page_id=119
template<class T>
inline Print &operator <<(Print &obj, T arg)
{ obj.print(arg); return obj; }

static uint8_t mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
static uint8_t ip[] = {192, 168, 0, 254};

dht DHT;
IPAddress home_server(192, 168, 0, 253); // home
EthernetClient client;

#define PREFIX ""
WebServer webserver(PREFIX, 80);

const int MAX_INPUT_CONTACTS = 5;
int input_contacts[MAX_INPUT_CONTACTS] = {24, 25, 30, 32, 34};
int input_contacts_last[MAX_INPUT_CONTACTS];
int input_contacts_now[MAX_INPUT_CONTACTS];

int a0_average = 0;
int a0_count = 0;
int a0_last = 0;

// Determine memory left
int freeRam () {
  extern int __heap_start, *__brkval; 
  int v; 
  return (int) &v - (__brkval == 0 ? (int) &__heap_start : (int) __brkval); 
}

void index(WebServer &server, WebServer::ConnectionType type, char *url_tail, bool tail_complete)
{
	if (type != WebServer::GET)
	{
		server.httpFail();
		return;
	}
	
	server.httpSuccess("text/html");
	
	server << F("<a href='http://192.168.0.253/mobile.php'>Go to mobile control</a><br/><br/><br/>");
        server << F("<a href='http://192.168.0.253/'>Go to home control</a><br/>");
}

void outputs(WebServer &server, WebServer::ConnectionType type, char *url_tail, bool tail_complete)
{
	if (type != WebServer::GET)
	{
		server.httpFail();
		return;
	}
	
	server.httpSuccess("text/html");

	if (strlen(url_tail))
	{
		URLPARAM_RESULT rc;
		char name[4];
		int  name_len;
		char value[4];
		int value_len;

		while (strlen(url_tail))
		{
			rc = server.nextURLparam(&url_tail, name, 4, value, 4);
			if (rc != URLPARAM_EOS)
			{
				int iName = atoi(name);
				switch(iName)
				{
                                        case 39: // garage door opener
                                        case 44: // master bath fan
					case 46:
					case 47:
					case 48:
					case 49:
						setPin(iName, atoi(value));
					break;
					
					default:
						// do nothing
					break;
				}
			}
		}
    }
    else
    {
    
    }
}

void humidity(WebServer &server, WebServer::ConnectionType type, char *url_tail, bool tail_complete)
{
	if (type != WebServer::GET)
	{
		server.httpFail();
		return;
	}
		
	server.httpSuccess("text/xml");
	
   switch (DHT.read22(22))
   {
		case DHTLIB_OK:
			server << F("<master-bathroom humidity=\"") << DHT.humidity << F("\" temperature=\"") << DHT.temperature  << F("\"/>");
		break;
		
		case DHTLIB_ERROR_CHECKSUM:
			server << F("<error>Checksum Error</error>");
		break;
		
		case DHTLIB_ERROR_TIMEOUT:
			server << F("<error>Timeout Error</error>"); 
		break;
		
		default:
			server << F("<error>Unknown Error</error>"); 
		break;
	}
}

void setPin(int pin, int state)
{
	switch(pin)
	{
                case 39: // garage door opener
                  if(state == LOW)
                  {
                    digitalWrite(pin, LOW);
                    delay(250);
                    digitalWrite(pin, HIGH);
                    if (client.connect(home_server, 80))
		    {
		    // Make a HTTP request:
	        	client << F("GET /arduino.php?action=pin-change&pin=") << pin << F("&state=") << state << F(" HTTP/1.0");
			client.println(); // finishes previous line
			client.println(F("User-Agent: arduino-ethernet"));
			client.println(F("Connection: close"));
			client.println(); // finishes request
			client.stop();
		    } 
                  }
                break;

                case 44: // master bath fan
		case 46:  // sprinklers
		case 47: // sprinklers
		case 48: // sprinklers
		case 49: // sprinklers
			if(state == 2)
			{
				state = !digitalRead(pin);
			}
			if((state == HIGH || state == LOW) && (state != digitalRead(pin)))
			{
				digitalWrite(pin, state);
				// Report change
				if (client.connect(home_server, 80))
				{
					// Make a HTTP request:
					client << F("GET /arduino.php?action=pin-change&pin=") << pin << F("&state=") << state << F(" HTTP/1.0");
					client.println(); // finishes previous line
					client.println(F("User-Agent: arduino-ethernet"));
					client.println(F("Connection: close"));
					client.println(); // finishes request
					client.stop();
				}
			}
		break;
		
		default:
			// skip it
		break;
	}
}

void status(WebServer &server, WebServer::ConnectionType type, char *url_tail, bool tail_complete)
{
	if (type != WebServer::GET)
	{
		server.httpFail();
		return;
	}
		
	server.httpSuccess("text/xml");
	
	server << F("<status version=\"") << VERSION << F("\" freeram=\"") << freeRam() << F("\" millis=\"") << millis() << F("\"/>");
}

void pinsXML(WebServer &server, WebServer::ConnectionType type, char *url_tail, bool tail_complete)
{
	if (type != WebServer::GET)
	{
		server.httpFail();
		return;
	}
		
	server.httpSuccess("text/xml");
	server << F("<pins><digital> ");

	int i;
	for (i = 0; i < 54; ++i)
	{
		int val = digitalRead(i);
		server << F("<pin id=\"") << i << F("\" state=\"") << val << F("\"/>");
	}
	server << F("</digital><analog>");

	for (i = 0; i <= 15; ++i)
	{
		int val = analogRead(i);
		server << F("<pin id=\"") << i << F("\" value=\"") << val << F("\"/>");
	}
	server << F(" </analog></pins>");
}

void setup()
{
	// Setup custom pins
	
	// Door/Window Contacts / doorbell
	for(int i = 0; i < MAX_INPUT_CONTACTS; i++)
        {
          pinMode(input_contacts[i], INPUT_PULLUP);
        }

        // Garage door opener
        pinMode(39, OUTPUT); digitalWrite(39, HIGH);
	
	// humidity sensors
	//pinMode(22, INPUT); //_PULLUP); // use INPUT_PULLUP when there is no resistor on the circuit
        pinMode(44, OUTPUT); digitalWrite(44, LOW);// master bath fan

	// sprinkler relays
	pinMode(46, OUTPUT); pinMode(47, OUTPUT); pinMode(48, OUTPUT); pinMode(49, OUTPUT);
	digitalWrite(46, HIGH); digitalWrite(47, HIGH); digitalWrite(48, HIGH); digitalWrite(49, HIGH);

	// Setup hardware pins
	pinMode(53, OUTPUT);     // ethernet shield sd card pin; 10 on uno, 53 on a mega
	
	// Start SD card
	if ( !SD.begin(4))
	{
		return;    // init failed
	}
	
	// Start Ethernet
	Ethernet.begin(mac, ip);

	// Setup default HTTP response
	webserver.setDefaultCommand(&index);
	webserver.addCommand("status", &status);
	webserver.addCommand("pins.xml", &pinsXML);

	webserver.addCommand("outputs", &outputs);
	webserver.addCommand("humidity", &humidity);

	// Start HTTP server
	webserver.begin();
	
	if (client.connect(home_server, 80))
	{
		// Make a HTTP request:
		client.println(F("GET /arduino.php?action=boot HTTP/1.0"));
		client.println(F("User-Agent: arduino-ethernet"));
		client.println(F("Connection: close"));
		client.println();
		client.stop();
	}
}


void loop()
{
	for(int i = 0; i < MAX_INPUT_CONTACTS; i++)
	{
		input_contacts_now[i] = digitalRead(input_contacts[i]);
	}
	if(memcmp(input_contacts_now, input_contacts_last, sizeof(input_contacts_last)) != 0)
	{
		if (client.connect(home_server, 80))
		{
			// Make a HTTP request:
			client << F("GET /arduino.php?action=pin-change&multi=1&pin=");
			for(int i = 0; i < MAX_INPUT_CONTACTS; i++)
			{
				if(input_contacts_now[i] != input_contacts_last[i])
				{
					client << F(",") << input_contacts[i] << F("-") << input_contacts_now[i];
				}
			}
			client << F(" HTTP/1.0");
			client.println(); // finishes previous line
			client.println(F("User-Agent: arduino-ethernet"));
			client.println(F("Connection: close"));
			client.println(); // finishes request
			client.stop();
		}
		memcpy(input_contacts_last, input_contacts_now, sizeof input_contacts_now);
	}
	
	// Water heater CT
	a0_average += analogRead(A0);
	if(++a0_count > 99)
	{
		a0_average = a0_average / 100;
		if((a0_last == 1 && a0_average == 0) || (a0_last == 0 && a0_average > 0))
		{
			if(a0_last == 1 && a0_average == 0)
			{
				a0_last = 0;
			}
			else if(a0_last == 0 && a0_average > 0)
			{
				a0_last = 1;
			}
			if (client.connect(home_server, 80))
			{
				// Make a HTTP request:
				client << F("GET /arduino.php?action=water-heater&status=") << a0_last;
				client << F(" HTTP/1.0");
				client.println(); // finishes previous line
				client.println(F("User-Agent: arduino-ethernet"));
				client.println(F("Connection: close"));
				client.println(); // finishes request
				client.stop();
			}
		}
		a0_average = 0;
		a0_count = 0;
	}

	char buff[64];
	int len = 64;
	
	// Process HTTP Requests
	webserver.processConnection(buff, &len);
}
