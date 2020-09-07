# test_task

So u can find description of task [here](TestTask.md)

# cover letter
I think it still not perfect code by the way and here some reasons:

1. Locks in storage implemented as example, of couse it should be fine locks based on redis or on db by ACID
1. Test coverage still incomplete. I will not finish it cos i spent too much time for this test task (around 16h to be honest)
1. Storages. I implemented key value storage based on json files. Its a  poor, cos json_decode is too heavy.
I think better solution is set up long term storage based a db, for bins  at least. And some storage like redis for currencies.
1. Tax intercepors and task calculator have to process some batch of transactions; it should be fixed first imo.
1. Resulted script: php app.php input.txt, so few words about code inside - i implement is such way cos no sense to create smth good without knowledge about how it will work futher.

# integration
I tried to use minimal count of dependencies.

So i created simple Application class, its just a mock which help show results.
So it might be put in trash in case of integration.


That code would really easy integrates with Symfony (maybe Laravel too) and any frameworks with solid dependency injection mechanics. 


P.S. I really think 2-4 hours is not enough for this task
