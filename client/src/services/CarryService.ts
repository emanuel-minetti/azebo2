import ApiService from "/src/services/ApiService";
import { Carry } from "/src/models";

export default class CarryService extends ApiService {
  static getCarryResultByMonth(params: string) {
    const url = this.getBaseUrl() + "carry-result/" + params;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }

  static getCarry() {
    const url = this.getBaseUrl() + "carry";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }

  static setCarry(carry: Carry) {
    const url = this.getBaseUrl() + "carry";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const day =
      "" +
      (carry.year.getDate() <= 9
        ? "0" + carry.year.getDate()
        : carry.year.getDate());
    const month =
      "" +
      (carry.year.getMonth() <= 9
        ? "0" + (carry.year.getMonth() + 1)
        : carry.year.getMonth() + 1);
    const year = carry.year.getFullYear();
    const yearString = [year, month, day].join("-");
    const carryObject = {
      id: carry.id,
      user_id: carry.user_id,
      year: yearString,
      saldo_hours: carry.saldo.hours,
      saldo_minutes: carry.saldo.minutes,
      saldo_positive: carry.saldo.positive,
      holidays: carry.holidays,
      holidays_previous_year: carry.holidaysPrevious,
    };
    const requestOptions = {
      method: "POST",
      headers: headers,
      body: JSON.stringify(carryObject),
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
