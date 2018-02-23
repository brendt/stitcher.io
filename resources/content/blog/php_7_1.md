As of version 1.0.0-alpha2, the minimum PHP requirement for the Stitcher library is 7.1. This decision was made because of two reasons:

- It would be a bad choice to keep supporting end of life- and deprecated PHP versions.
- Because PHP 7.0 lacks void and nullable type hints, 7.1 is a better choice.

Because this library is still in early development, I feel its better to make the right decision early on, 
rather than having trouble in the future with updates. I'll also be working on updating the current code to PHP 7.1 standards.
