# Change Log
All notable changes to this project will be documented in this file.
Note that the SDK calls files that are provided by a service that is under development. Thus, changes in the service may be reflected in the pages using the SDK when no actual change have been performed.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
### To Add
- Asset version number is managed in the getAssetsRequest - we need this to make sure that changes in the API do not break existing implementations.
	=> pending implementation from the API side. 
- Manage multi language configuration. 
	- The getAssetsResponse::getJsUrl function should take a $locale variable to return a file corresponding to the user environment language.
	- Find and include language specific initializations for used languages (es, ca, en)
	- setup javascript included file to read query string and display dates in specified language.
- change repo name to recs-merchant-sdk-php & update composer.json accordingly.
- Get list of operating countries from an API call.

### To Fix
- Work with a tag an not a helper to display html. Full html should be initialized via javascript.

### [1.1.13] - 2015-11-27
### Fix
- in LastMileBestPriceResponse, modified getBestPriceCurrency method to return currency code, and not currency sign. Added other method to get the sign.

### [1.1.12] - 2015-11-27
### Fix
- Set staging environment to herokuapp.com domain instead of nektria.com
- Refactored Client class
### Added
- Removed secure parameter - all requests to staging and prod go over https.

## [1.1.11] - 2015-11-27
### Added
- Added currency_code parameter in the service creation request.

## [1.1.10] - 2015-11-24
### Added
- Added session_timeout parameter in the service creation request.


## [1.1.9] - 2015-11-23
### Fix
- Removed composer.lock from the project

## [1.1.8] - 2015-11-23
### Added
- New parameters for language and version in assets retrieval process. To start with, version should be '1' and language should be 'es_ES'.

## [1.1.7] - 2015-11-20
### Added
- API calls timeout
- options to control these timeouts

## [1.1.6] - 2015-11-19
### Fix
- getRegistrationUrl is not asking for API key anymore.

## [1.1.5] - 2015-11-18
### Fix
- set APIKEY compulsory parameter to empty value for getRegistrationUrl (where no api key is needed).

## [1.1.4] - 2015-11-12
### Fix
- tipo en LastMileBestPriceRequest

## [1.1.3] - 2015-11-11
### Added
- Registration Access Request

## [1.1.2] - 2015-11-10
### Added
- README: Explained how registration link request will work. 
	
## [1.1.1] - 2015-11-09
### Added
- Functionality: Set up lastMileBestPriceRequest and LastMileBestPriceResponse
- README: 
	- use of lastMileBestPriceRequest description 
	- widget javascript function to update calendar with previously selected timeslots.

### Fixed	
- README: layout for correct pdf display

## [1.1] - 2015-10-23
### Added
- Set production to point to production environment.

## [1.0.2.alpha] - 2015-10-23
### Added
- Backend Access for eCommerce Advanced Configuration is now available.

## [1.0.1.alpha] - 2015-10-22
### Added
- README: details about how to get list of operating countries and how to setup backend access for advanced configuration.
- Set up objects to return list of operating countries (hardcoded).

## [1.0.alpha] - 2015-10-08
### Added
- Exception dealing with api responses, providing all information we get from the server.


## [0.9.3] - 2015-10-07
### Fixed
- Validation Request execute method now accepts both plain json string and array format as parameters. Readme suggests plain json string should be used.

## [0.9.2] - 2015-10-07
### Fixed
- Fixed missing use statement for exceptions in Response Body Wrapper.

## [0.9.1] - 2015-10-07
### Fixed
- Fixed Guzzle throwing own exceptions. Now all exceptions thrown by the SDK are children of Nektria\Recs\MerchantApi\Exceptions\ApiClientException

## [0.9] - 2015-10-06
### Added
- Show Shipping Request Helper provides methods to show localized windows and status information.

## ? - ?
### Fixed
- Submit button (Confirmar)

## [0.8] - 2015-09-23
### Added
- Show Shipping Request.

## [0.7.6] - 2015-09-25
### Changed
- Updating readme to reflect changes with javascript callback function.
- Added checkup of basket shipping price on server side.

## [0.7.5] - 2015-09-15
### Added
- Configuration parameters for environments and secure urls


## [0.7.4] - 2015-09-15
### Added
- Last Mile Confirmation Response now deals with response content.

## [0.7.3] - 2015-09-15
### Fixed
- proof read README.md

## [0.7.2] - 2015-09-15
### Fixed
- order_number in the confirmation messages is taken into account for when the order is created (last mile).

## [0.7.1] - 2015-09-15
### Fixed
- changed service url for nektria recs api staging url.
- Last mile availability response data is returned by javascript method call.
- Ids are Initialized in the array. Data provided by previous method call is injected.
- get order_number in the confirmation message
- typo in ClassicAvailabilityRequest (missing new).

### Added
- modal displays information about terms and conditions and FAQs.
- unselect all button works.

## [0.7] - 2015-09-15
### Added
- getPriceMatrix built with response from the availability request (used to be dummy data).
- Time Windows chosen by the shopper is returned by the getUserCalendarSelection method.
- Availabilities submitted to the addOnloadInit method are displayed in the calendar.
- Calendar appear without prices for shopper preselection, and after clicking "ver precios de envio" it appears with the prices. Both selections are stored and returned later.

### Fixed
- fixed wrong index in ServiceCreationResponse

## [0.6.1] - 2015-09-15
### Changed

- order_number is no more compulsory in service request

## [0.6] - 2015-09-04
### Changed
- updated README.md to reflect new format in js asset: function calls prefixed by recs_ have been replaced by method calls of the object nektria_recs without prefix. Example: recs_showTimeWindowArea is now recs_nektria.showTimeWindowArea().
- nektria_recs.getTotalPrice() has been added and documented in the README.md, step 8).
- created CHANGELOG.md

## [0.2] - 2015-09-03
### Changed
- API side: all css styles now are prefixed with the id of the div containing the widget: rec-timewindow.
- recs_getTotalPrice() (now nektria_recs.getTotalPrice()) function in the javascript asset provided by the API is now returning the actual total price. On the other hand, it has been documented in the README.md
- updated test.php so that it works fine with newly released API.

## [0.1] - 2015-08-22
### Added
- first release

	
[0.1]: https://bitbucket.org/nektria/merchant-api-client/commits/1e0b69613401a2e58dde192c0fd94ef4fa5bb2ae
[0.2]: https://bitbucket.org/nektria/merchant-api-client/commits/27fb81a76df2ce2d77fff94248909769d91c9dae
[0.3]: #
