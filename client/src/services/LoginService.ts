export default class LoginService {
  static login(username: String, password: String) {
    // eslint-disable-next-line no-console
    console.log(JSON.stringify({ username, password }));
    const requestData = JSON.stringify({
      username,
      password
    });
    const requestOptions = {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
      },
      body: requestData
    };
    //TODO make configurable
    return fetch("http://localhost:7000/api/login", requestOptions)
      .then(answer => {
        // eslint-disable-next-line no-console
        console.log(answer);
      })
      .catch(answer => {
        // eslint-disable-next-line no-console
        console.log(answer);
      });
};}