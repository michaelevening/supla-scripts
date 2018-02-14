angular.module('supla-scripts').component 'timeRangePicker',
  templateUrl: 'app/thermostat/profiles/time-range-picker.html'
  require:
    ngModel: 'ngModel'
  controller: (minutesToHumanTimeFilter, humanTimeToMinutesFilter) ->
    new class
      constructor: ->
        @sliderOptions =
          floor: 0
          ceil: 24 * 60
          step: 5
          minRange: 15
          translate: minutesToHumanTimeFilter
          onChange: @updateNgModel

      updateNgModel: =>
        @ngModel.$setViewValue
          timeStart: minutesToHumanTimeFilter(@timeRange.timeStart)
          timeEnd: minutesToHumanTimeFilter(@timeRange.timeEnd)

      $onInit: ->
        @timeRange =
          timeStart: 0
          timeEnd: 24 * 60
        @ngModel.$render = =>
          if @ngModel.$viewValue
            angular.extend @timeRange,
              timeStart: humanTimeToMinutesFilter(@ngModel.$viewValue.timeStart or '00:00')
              timeEnd: humanTimeToMinutesFilter(@ngModel.$viewValue.timeEnd or '23:59')
            @timeRange.timeEnd = 1439 if @timeRange.timeEnd == 0
          else
            @updateNgModel()
