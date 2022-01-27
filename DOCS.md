# LoRa Performance Tool APIs

## 1. General

### 1.1. Headers
* Authorization: Basic base64encode({accessToken})
  * `accessToken` is set in `config/users.php` per user.

### 1.2. Output on error (JSON)
* name – error type
* message – error description
* code – internal error code
* status – http error code
* type – framework error type


## 2. Accuracy Histogram API
Get the Geolocation accuracy histogram for a given device group and a given time period. Bins thresholds can be configured.
* URL {{baseUrl}}/rest/accuracy-histogram
* Method  GET

### 2.1. Query parameters
* deviceGroupId (required if deviceId not set) – ID of device group to get results from
* devEUI (required if deviceGroupId not set) – DevEUI of device to get results from
* startDateTime (optional, default: 2 weeks ago) – yyyy-mm-dd HH:MM:SS
* endDateTime (optional, default: now) – yyyy-mm-dd HH:MM:SS
* bins (optional, default: 150,1000) – comma separated sorted integer list ex: 100,300,1000

### 2.2. Output on success (JSON)
* input – variables used to calculate output
* device – (when devEUI is set) details of device
* deviceGroup – (when deviceGroupId is set) details of device group
* weblink – (when deviceGroupId is set) link to accuracy histogram page in web interface
* output – data output, an array with values for each bin:
  * data groups:   
    * aggregated – the aggregated data 
    * daily – the data per day in the selected period 
  * data points:
    * label of bin 
    * upperBoundary of bin 
    * nrFrames in the bin 
    * percentage of occurrence in the bin


## 3. Daily statistics API
Get daily statistics for a given device group and a given time period
* URL  {{baseUrl}}/rest/daily-stats
* Method  GET

### 3.1. Query parameters
* deviceGroupId (required if deviceId not set) – ID of device group to get results from
* devEUI (required if deviceGroupId not set) – DevEUI of device to get results from
* startDateTime (optional, default: 2 weeks ago) – yyyy-mm-dd HH:MM:SS
* endDateTime (optional, default: now) – yyyy-mm-dd HH:MM:SS

### 3.2. Output on success (JSON)
* input – variables used to calculate output
* device – (when devEUI is set) details of device
* deviceGroup – (when deviceGroupId is set) details of device group
* weblink – (when deviceGroupId is set) link to accuracy histogram page in web interface
* output – data output
  * data groups:
    * aggregated – the aggregated data
    * daily – the data per day in the selected period
  * data points:
    * nr_frames – nummer of frames in the data group 
    * drop_rate – drop rate of frames, in percentage 
    * geoloc_acc_median_avg – average of the median accuracy of the sessions in the results, in meters 
    * geoloc_acc_avg – total average accuracy of all frames in the data group, in meters 
    * geoloc_success_rate – total success rate of geoloc for frames in the data group, 0 to 1 
    * gw_count_avg – average gateway count 
    * snr_avg – average signal to noise ratio


## 4. API – Get list of resources Frames/Sessions/Devices
Get a list of certain resources that are stored in the LoRa Performance Tool.
* URL 
  * Frames  {{baseUrl}}/rest/frames
  * Sessions {{baseUrl}}/rest/sessions
  * Devices  {{baseUrl}}/rest/devices
* Method   GET

###  4.1. Query parameters
* sort (optional) – sort by a certain value. 
  * Example: ?sort=-id¬ – to sort by id descending.
* page (optional) – determines the page of results to get (starting at 1). 
  * Example: ?page=10 – will get you the 10th page of results.
* per-page (optional) – determines the number of results per page. 
  * Example: ?per-page=25 – will get you 25 results.

### 4.2. Output on success (JSON)
* items – your results
* _links – HATEOS links to other results
* _metadata – information about the results you get 
  * totalCount – total number of results in your request 
  * pageCount – total number of pages for your request 
  * currentPage – current page of results 
  * perPage – number of results in a page

### 4.3. Not (yet) supported
* Filtering


## 5. API – Get a certain resource Frames/Sessions/Devices
Get a certain resource that is stored in the LoRa Performance Tool.
* URL
  * Frames  {{baseUrl}}/rest/frames/:id 
  * Sessions {{baseUrl}}/rest/sessions/:id
  * Devices  {{baseUrl}}/rest/devices/:id
* Method   GET

### 5.1. Path variable
* id (required) – the id of the resource you want to fetch

### 5.2. Output on success (JSON)
* Your requested resource.
