export default class LoginService {
  static login(username: String, password: String) {
    const requestData = JSON.stringify({
      username,
      password
    });
    const requestOptions = {
      method: "POST",
      headers: {
        "Content-Type": "application/json", // header name contains a hyphen, so quotes are required
        Accept: "application/json"
      },
      body: requestData
    };
    //TODO make configurable
    return fetch("http://localhost:7000/api/login", requestOptions)
      .then(this.handleResponse)
      .then(user => {
        if (user.jwt) {
          localStorage.setItem("user", JSON.stringify(user));
        }
        return user;
      });
  }

  private static handleResponse(response: Response) {
    return response.text().then(text => {
      const content = text && JSON.parse(text);
      if (!response.ok) {
        if (response.status == 401) {
          LoginService.logout();
          location.reload();
        }
        const error = (content && content.message) || response.statusText;
        return Promise.reject(error);
      }
      return content.data;
    });
  }

  private static logout() {
    localStorage.removeItem("user");
  }
}
