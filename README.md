# Command Query Generator
## ZF2 Module for [Tactician](http://tactician.thephpleague.com)

Module for ZF2 that allows to create command and handler from **CLI**.

### Usage

Cammand generation is invokes as other ZF2 CLI controllers via `php public/index.php ...`

`generate command --module= --name=`

`--module` - Module name (Example: Application)

`--name` - Command name (Example: User/Create, CreateUser, Domain/Create/User)

NOTE: Command name parameter uses shashes (/) to create subdirectories.
