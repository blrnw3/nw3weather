**Site V4

nw3weather goes OO.

Hopefully this will make for quicker, easier development in the future.

I investigated using a framework, but the unique nature of this system disfavours such an approach.
Frameworks have a lot of MVC bloat that makes life easy for CRUD applications, which this is not.
However, I will probably use some database abstraction libraries as writing raw SQL is both ugly and a pain. 
I may also use a URL router.