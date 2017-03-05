PrivateService = ($http, $q) ->

  getServicesAvailable = () ->
    []

  # ---- Add a private sesion #
  addSesion = (obj) ->
    defered = $q.defer()

    $http.post(config.Url + 'addSesion/', $.param(obj), config.post)
      .then (data) ->
        defered.resolve data.data
        return
    defered.promise
  
  # ---- #
  return {
    UserName: 'cliente 2'
    GetServicesAvailable: getServicesAvailable
    AddSesion: addSesion
  }

angular
	.module('appAgenda')
	.service('PrivateService', PrivateService)
PrivateService
  .$inject = ['$http', '$q']