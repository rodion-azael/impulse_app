agendaSemiCtrl = ($state, User, Center, SemiService) ->
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

  # ---- #
  dataToCalendar = () ->
    vm.events = []
    for app in vm.Cabina.Appnmts
      vm.events.push {
        id: app.id
        title: app.title,
        user_id: app.user_id
        startsAt: new Date(app.start)
        endsAt: new Date(app.end)
        draggable: false
      }
    vm.doneLoading = true
    return

  # ---- Load all cabina details #
  getCabinaDetails = () ->
    cabinaID = do Center.SelectedCabina
    date = moment(vm.appDate).format('YYYY/MM/DD')
    Center.GetCabinaDetails(cabinaID, date)
      .then (data) ->
        vm.Cabina = data.data
        vm.Apertura = vm.Cabina.Details[0].Apertura
        vm.Cierre = vm.Cabina.Details[0].Cierre
        do dataToCalendar
        return
    return

  # ---- #
  includeInSemi = (event) ->
    $state.go 'addToSemi', {id: event.id}
    return

  # ---- An empty slot clicked. Creating new private sesion #
  newSemiSesion = (timespan) ->
    if confirm '¿Desea agendar una sesión para el dia ' + timespan.format('DD MMM YYYY') + ' a las ' + timespan.format('hh:mm') + '?'
      obj = {
        start: timespan.format('YYYY-MM-DD HH:mm:ss')
        end: timespan.add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss')
        DURACION_CITA: 30,
        user: User.User.client_id
        type: '26'
      }
      SemiService.AddSesion(obj)
        .then (data) ->
          alert 'Se ha creado la sesión. Utiliza el código ' + data.code + ' para invitar a mas personas a esta sesión.'
          do getCabinaDetails
          return
    return

  # ---- Initial loading function #
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
  vm.newSemiSesion = newSemiSesion
  vm.includeInSemi = includeInSemi

  # ---- Calendar vars #
  vm.calendarView = "day"
  vm.Apertura = '08:00:00'
  vm.Cierre = '19:00:00'


  do vm.init
  return

angular
  .module('appAgenda')
  .controller('agendaSemiCtrl', agendaSemiCtrl)

agendaSemiCtrl
  .$inject = ['$state', 'UserData', 'CenterDataService', 'SemiService']