agendaGrupalAddCtrl = ($state, User, GrupalService) ->
  vm = @  

  init = () ->
    do vm.UserIsOnGroup

  UserIsOnGroup = () ->
    obj = {
      start: vm.start
      cbn: '25'
    }
    GrupalService.UserIsOnGroup obj
      .then (data) ->
        if data.success == "true" then vm.UserIsOnSession = true
        vm.Loading = false
    return

  addTosession = () ->
    obj = {
      start: moment($state.params.span.startsAt).seconds(0).format('YYYY-MM-DD HH:mm:ss')
      end: moment($state.params.span.startsAt).seconds(0).add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss')
      DURACION_CITA: 30,
      user: User.User.client_id
      type: '25'
    }
    GrupalService.AddToSesion obj
      .then (data) ->
        if data.success == true then vm.UserIsOnSession = true
    return


  vm.start = moment($state.params.span.startsAt).seconds(0).format('YYYY-MM-DD HH:mm:ss')
  vm.UserIsOnSession = false
  vm.Loading = true

  vm.title = $state.params.span.title

  vm.init = init
  vm.UserIsOnGroup = UserIsOnGroup
  vm.addTosession = addTosession

  do vm.init
  return

angular
  .module('appAgenda')
  .controller('agendaGrupalAddCtrl', agendaGrupalAddCtrl)

agendaGrupalAddCtrl
  .$inject = ['$state', 'UserData', 'GrupalService']