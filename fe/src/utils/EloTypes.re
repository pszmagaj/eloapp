type user = {
  userNid: int,
  code: string,
  name: string,
  rating: int,
};

type ratingHistory = {
  rating: int,
  date: string,
};

type ratingHistoryWithWin = {
  rating: int,
  date: string,
  isWin: bool,
};

type containerActions =
  | GetUsersSvc
  | SetUsersToState(list(user));

type appContainerActions =
  | IsLogged
  | SetIsLogged(bool);

type winnerLooserNids = {
  winnerUserNid: int,
  looserUserNid: int,
};