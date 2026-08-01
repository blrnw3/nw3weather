Site V4
-------

nw3weather goes OO.

Hopefully this will make for quicker, easier development in the future.

I investigated using a framework (in particular CakePHP, which I have in fact downloaded to get some inspiration from their code),
but the non-standard nature of this system disfavours such an approach;
frameworks have a lot of MVC bloat that makes life easy for CRUD applications, which this is not.
So for now I am only using native PHP libraries like PDO, and simplying borrowing the best bits of Cake that I need.