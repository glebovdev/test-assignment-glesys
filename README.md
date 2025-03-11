## Getting started
1. Run `make` to install dependencies and run database migrations
2. Run `php api/artisan key:generate`
3. Run `php api/artisan serve` or `make upd` to start app locally

## Assignment
You will be implementing a health application that receives "heartbeats" from other applications through a GraphQL mutation. You'll also implement a GraphQL query that returns unhealthy heartbeats. Documention on working with our GraphQL package can be found [here](https://github.com/glesys/butler-graphql).

### Step 1
Implement the `sendHeartbeat` mutation as defined in `schema.graphql`. The mutation takes the following input:

- `applicationKey` — Key uniquely identifying an application e.g. `app-1`.
- `heartbeatKey` — Key uniquely identifying a heartbeat for a given application e.g. `sync-job`.
- `unhealthyAfterMinutes` — Integer specifying after how many minutes a heartbeat should be seen as unhealthy. For example if `unhealthyAfterMinutes` is set to 5 minutes and it has been 10 minutes since we received the latest heartbeat the heartbeat is unhealthy.

Add database tables and models as needed. Write tests for the mutation covering relevant scenarios and use cases.

### Step 2
Implement the `unhealthyHeartbeats` query as defined in `schema.graphql`. As the name suggests it should return all heartbeats that are unhealthy. It also takes an optional filter parameter `applicationKeys`. If the filter parameter is set to e.g. `["app-1", "app-2"]` then we should only return unhealthy hearbeats belonging to `app-1` and `app-2`.

Add database tables and models as needed. Write tests for the query covering relevant scenarios and use cases.

