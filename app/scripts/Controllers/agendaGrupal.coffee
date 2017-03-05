agendaGrupalCtrl = ($state, User, GrupalService) ->
  vm = @

  # ---- Change Calendar pick plugin flag to open/close #
  openCalendar = () ->
    vm.calendarOpened = true
    return

  # ---- Change the date #
  changeDate  = () -> 
    vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY')
    do getCabinaDetails
    return

  # ---- Converts service response to Calendar obj #
  dataToCalendar = () ->
    vm.events = []
    for app in vm.Cabina
      hourStart = app.start.substring(0, 2)
      minStart = app.start.substring(3, 5)
      hourEnd = app.end.substring(0, 2)
      minEnd = app.end.substring(3, 5)
      vm.events.push {
        id: app.id
        title: app.title + ' -- Disponibles: ' + app.available,
        startsAt: new Date(vm.appDate).setHours(parseInt(hourStart), parseInt(minStart))
        endsAt: new Date(vm.appDate).setHours(parseInt(hourEnd), parseInt(minEnd))
        draggable: false
      }
    vm.doneLoading = true
    return

  # ---- #
  addToGroupSesion = (timespan) ->
    $state.go 'grupal-add', {span: timespan}
    return
    #if confirm '¿Desea agendar una sesión para el dia ' + timespan.format('DD MMM YYYY') + ' a las ' + timespan.format('hh:mm') + '?'
    #  obj = {
    #    start: timespan.format('YYYY-MM-DD HH:mm:ss')
    #    end: timespan.add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss')
    #    DURACION_CITA: 30,
    #    user: User.User.client_id
    #    type: '25'
    #  }
    #  GrupalService.AddToSesion(obj)
    #    .then (data) ->
    #      do getCabinaDetails
    #      return
    #return

  # ---- Load all cabina details #
  getCabinaDetails = () ->
    obj = {
      day: moment(vm.appDate).day()
      date: moment(vm.appDate).format('YYYY-MM-DD')
      cbn: '25'
    }
    GrupalService.GetDaySchedule(obj)
      .then (data) ->
        vm.Cabina = data.obj
        do dataToCalendar
        return 
    return

  init = () ->
    if User.IsLogged is false
      alert 'No ha iniciado sesion'
      return false
    do getCabinaDetails
    return

  # ---- Declarations #
  # ---- Pass to View Model (vm) only those funcitons or variables needed on the html #
  vm.init = init
  vm.calendarOpened = false
  vm.appDate = new Date()
  vm.openCalendar = openCalendar
  vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY')
  vm.changeDate = changeDate
  vm.Cabina = {}
  vm.events = []
  vm.doneLoading = false

  vm.addToGroupSesion = addToGroupSesion

  vm.calendarView = "day"
  vm.Apertura = '08:00:00'
  vm.Cierre = '19:00:00'

  do vm.init
  return

angular
  .module('appAgenda')
  .controller('agendaGrupalCtrl', agendaGrupalCtrl)

agendaGrupalCtrl
  .$inject = ['$state', 'UserData', 'GrupalService']