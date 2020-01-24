import { LoginService } from "@/services";

export default class ApiService {
  /**
   * Returns the base URL of the webapp.
   */
  protected static getBaseUrl() {
    const loc = window.location;
    return loc.protocol + "//" + loc.host + "/api/";
  }

  /**
   * Returns the headers needed for API requests.
   */
  protected static getHeaders() {
    return {
      "Content-Type": "application/json", // header name contains a hyphen, so quotes are required
      Accept: "application/json",
      "Cache-Control": "no-cache"
    };
  }

  /**
   * Returns the authentication header.
   */
  protected static getAuthHeader() {
    let jwt = localStorage.getItem("jwt");
    if (jwt) {
      jwt = jwt.substring(1, jwt.length - 1);
      return {
        Authorization: "Bearer " + jwt
      };
    } else {
      return {};
    }
  }

  /**
   * Extracts the actual data from a given response and returns it.
   *
   * Also refreshes the JWT if such was returned from the server.
   * @param response the response to handle
   */
  protected static handleResponse(response: Response) {
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
      // reset jwt
      if (content.data.jwt && content.data.expire) {
        localStorage.setItem("jwt", JSON.stringify(content.data.jwt));
        localStorage.setItem("expire", JSON.stringify(content.data.expire));
      }
      return content.data;
    });
  }
}
