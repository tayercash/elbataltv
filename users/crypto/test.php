<?php
header("Content-Type:application/json");
echo hash_equals(
    "8d63fa519922582e051a754b80ad1f0107cb3fc66b3c85faf37e8463d2773e81eb069cb1b7a103c0c1d998c3e7d90540f411a2b07d019e583a119afb2c5c534c",
    "804e27e8d15d452d3796011d31282b6d0c7f7d64595ce8b345fd122c8e15c5f089a9e9dae24f3ed982d4c90d659d1bdce1ca15a11414de3620034ddc188799fe"
) ? "true" : "false";

