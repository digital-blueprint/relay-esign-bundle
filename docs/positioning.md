# Signature Block Positioning

In addition to a signature profile and the actual PDF payload the signature
endpoints also take positioning data which determines where the signature block
will be placed on the PDF document.

If no positioning data is given it will default to append the signature block
after the content, either on the last page, or if there isn't enough space on
the last page then on a newly added page at the end.

The position units are in "points" using the PDF coordinate system which starts
at the bottom left corner of each page. The point unit can be converted to e.g.
cm via the DPI, for example "1cm ~ 28.3465pt".

* `p` - The page number where the signature block should be placed, starting
  with 1. A page number larger than the actual page count will append a new page
  instead. Defaults to the last page, or a newly added page if there isn't
  enough space.
* `x` - Position of the signature's top left corner in points, from the left of
  the page. Defaults to the signature block being centered horizontally.
* `y` - Position of the signature's top left corner in points, from the bottom
  of the page. Defaults to right after the content on the selected page, or at
  the top if there is not enough free space.
* `w` - Width of the signature block in points. Defaults to the default width
  specified in the backend. Note that making it too small might break the
  internal layout.
* `r` - Absolute rotation of the signature block in degrees, counterclockwise.
  Defaults to 0.
