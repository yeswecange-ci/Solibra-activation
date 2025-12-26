import json

# Lire le flow existant
with open('docs/twilio/flow_intelligent_v2_production.json', 'r', encoding='utf-8') as f:
    flow = json.load(f)

# Modifier la description
flow['description'] = "FlowIntelligentV3 - Flow avec demande de nom/pseudo en premier (PRODUCTION)"

# Trouver l'index du state "send_message_1"
send_message_1_index = next(i for i, state in enumerate(flow['states']) if state['name'] == 'send_message_1')

# Modifier send_message_1 pour aller vers msg_demande_nom au lieu de function_1
flow['states'][send_message_1_index]['transitions'] = [
    {
        "next": "msg_demande_nom",
        "event": "sent"
    },
    {
        "next": "msg_demande_nom",
        "event": "failed"
    }
]

# Créer les nouveaux states
new_states = [
    {
        "name": "msg_demande_nom",
        "type": "send-and-wait-for-reply",
        "transitions": [
            {
                "next": "set_user_name",
                "event": "incomingMessage"
            },
            {
                "next": "http_log_timeout",
                "event": "timeout"
            },
            {
                "next": "http_log_error",
                "event": "deliveryFailure"
            }
        ],
        "properties": {
            "offset": {
                "x": -610,
                "y": 1050
            },
            "from": "{{flow.channel.address}}",
            "body": "👤 Pour commencer, quel est ton nom ou pseudo ?\n\n(Tu peux utiliser ton vrai nom ou un surnom)",
            "timeout": "3600"
        }
    },
    {
        "name": "set_user_name",
        "type": "set-variables",
        "transitions": [
            {
                "next": "http_save_name",
                "event": "next"
            }
        ],
        "properties": {
            "variables": [
                {
                    "type": "string",
                    "value": "{{widgets.msg_demande_nom.inbound.Body}}",
                    "key": "user_name"
                }
            ],
            "offset": {
                "x": -610,
                "y": 1300
            }
        }
    },
    {
        "name": "http_save_name",
        "type": "make-http-request",
        "transitions": [
            {
                "next": "function_1",
                "event": "success"
            },
            {
                "next": "function_1",
                "event": "failed"
            }
        ],
        "properties": {
            "offset": {
                "x": -610,
                "y": 1550
            },
            "method": "POST",
            "content_type": "application/json",
            "add_twilio_auth": False,
            "body": "{\"phone\": \"{{flow.variables.phone_number}}\", \"name\": \"{{flow.variables.user_name}}\", \"timestamp\": \"{{flow.variables.timestamp}}\"}",
            "url": "https://app-can-solibra.ywcdigital.com/api/can/inscription-simple"
        }
    }
]

# Trouver l'index de function_1
function_1_index = next(i for i, state in enumerate(flow['states']) if state['name'] == 'function_1')

# Insérer les nouveaux states juste avant function_1
for i, new_state in enumerate(new_states):
    flow['states'].insert(function_1_index + i, new_state)

# Ajuster l'offset de function_1 pour qu'il soit en dessous
function_1_new_index = next(i for i, state in enumerate(flow['states']) if state['name'] == 'function_1')
flow['states'][function_1_new_index]['properties']['offset'] = {
    "x": -610,
    "y": 1800
}

# Écrire le nouveau flow
with open('docs/twilio/flow_with_name_v3_production.json', 'w', encoding='utf-8') as f:
    json.dump(flow, f, indent=2, ensure_ascii=False)

print("OK - Nouveau flow cree: docs/twilio/flow_with_name_v3_production.json")
print("\nModifications:")
print("1. send_message_1 -> msg_demande_nom (demande nom/pseudo)")
print("2. set_user_name (stocke le nom)")
print("3. http_save_name (sauvegarde le nom en BD)")
print("4. function_1 -> msg_question_1 (continue normalement)")
