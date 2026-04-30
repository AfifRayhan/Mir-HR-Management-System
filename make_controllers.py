import re

with open('app/Http/Controllers/RosterController.php', 'r') as f:
    content = f.read()

content = content.replace('class RosterController extends Controller', 'class DriverRosterController extends Controller')
content = re.sub(r"const GROUP_MAP = \[.*?\];", "const GROUP_MAP = [\n        'drivers' => 'Drivers',\n    ];", content, flags=re.DOTALL)
content = content.replace("$data['routePrefix'] = 'roster.';", "$data['routePrefix'] = 'driver-roster.';")
content = content.replace("$data['pageTitle'] = 'Roster';", "$data['pageTitle'] = 'Driver Roster';")

with open('app/Http/Controllers/DriverRosterController.php', 'w') as f:
    f.write(content)

with open('app/Http/Controllers/Roster/RosterTimeController.php', 'r') as f:
    content_time = f.read()

content_time = content_time.replace('class RosterTimeController extends Controller', 'class DriverRosterTimeController extends Controller')
content_time = re.sub(r"const GROUP_MAP = \[.*?\];", "const GROUP_MAP = [\n        'drivers' => 'Drivers',\n    ];", content_time, flags=re.DOTALL)
content_time = content_time.replace("$routePrefix = 'roster.';", "$routePrefix = 'driver-roster.';")
content_time = content_time.replace("$pageTitle = 'Roster';", "$pageTitle = 'Driver Roster';")
content_time = content_time.replace("route('roster.times.index')", "route('driver-roster.times.index')")

with open('app/Http/Controllers/Roster/DriverRosterTimeController.php', 'w') as f:
    f.write(content_time)
