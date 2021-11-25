# 2. Allow empty basket

Date: 2021-05-05

## Status

Accepted

## Context

This is required by Dixons in case of a "finance first" journey.

The `application-api` allows the creation of an application with an empty basket. However, the creation of an application requires a call to the `calculator-api-pub` to calculate the terms of the finance option selected. This call requires the purchase price for the application which is the sum of all the prices for every item in the basket. When the basket is empty the purchase price amount is 0. The `calculator-api-pub` returns a 400 error in this situation.

When successful, the response from the `calculator-api-pub` is stored in a new record in the `application_terms` table and linked to the application ID.

This has apparently never happened before since the purchase price was set directly on the application and not as a result from the sum of the prices of every single item.

## Decision

We don't want to change the `calculator-api-pub` as it's just a wrapper to a library which is distributed via CDN to the calculator/widgets used by many integrations (or at least this is our current understanding).

We decided that we don't call the `calculator-api-pub` when the purchase amount is 0, but we still want to have a record created in the `application_terms` table but with the `terms` column set to an empty object `{}`. This column will be populated as needed during the application workflow.

## Consequences

The `terms` column in the `application_terms` table will require to be populated properly once we have a purchase price that can be used for calling the `calculator-api-pub`.

