# Slurp : Simple PHP ELT with Validation

[![Build Status](https://travis-ci.org/courtney-miles/slurp.svg?branch=master)](https://travis-ci.org/courtney-miles/slurp) [![Coverage Status](https://coveralls.io/repos/github/courtney-miles/slurp/badge.svg?branch=master)](https://coveralls.io/github/courtney-miles/slurp?branch=master)

Slurp is a simple PHP ETL (extract, transform, load) tool that can validate prior to loading source data.

## Known limitations

* A CSV file with an inconsistent number of values per row will be silently handled, where it would be preferable to raise an exception.
