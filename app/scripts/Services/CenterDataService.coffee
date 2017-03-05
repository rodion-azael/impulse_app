CenterDataService = ($http, $q) ->

  vm = @
  selectedCabina = ''

  # ----- #
  getCentros = () ->
    return{
      '1': 'MIAMI'
    }

  # ----- #
  getSelectedCabina = () ->
    vm.selectedCabina

  # ----- #
  selectCabina = (id) ->
    vm.selectedCabina = id
    return

  # ----- #
  getCabinas = (id) ->
    defered = $q.defer()
    $http.get(config.Url + 'getCabinas/' + id)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise

  # ----- #
  getCabinaDetails = (id, date) ->
    defered = $q.defer()
    $http.get(config.Url + 'getCabinaDetails/' + id + '/' + date + '/')
      .then (data) ->
        defered.resolve data
        return
    defered.promise

  # ----- Vars that need to behave statically across the app  #
  # ----- Factory is always static, but the use of a local vm #
  # ----- object is to help them keep separate from the rest  #
  # ----- of the definitions.                                 #
  vm.selectedCabina = selectedCabina

  return {
    GetCabinas: getCabinas 
    SelectCabina: selectCabina
    SelectedCabina: getSelectedCabina
    GetCabinaDetails: getCabinaDetails
  }


angular
	.module('appAgenda')
	.factory('CenterDataService', CenterDataService)
CenterDataService
  .$inject = ['$http', '$q']